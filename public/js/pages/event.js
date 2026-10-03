// Eseményoldal (nyilvános): az esemény JSON-t API key-jel olvassa a Drive-ból, és pollinggal frissít.
// Formátum: docs/EVENT_FORMAT.md
import { cfg, DRIVE, HLS_JS } from 'soslive/lib/config.js';
import { coord, displayName, el, formatTime, loadScript, mapLink, safeUrl } from 'soslive/lib/dom.js';
import { gapi } from 'soslive/lib/http.js';
import { createRouteMap } from 'soslive/lib/map.js';

// Ennyi ideje frissült fájl számít „élőnek” (a mobil app 30 mp-enként küld pozíciót).
const LIVE_WINDOW_MS = 90 * 1000;

/**
 * Videó és linkek: `stream` = közvetlenül lejátszható URL (HLS / MP4, üres is lehet), `page` = a stream szolgáltató
 * nézői oldala (pl. YouTube / Twitch – nem biztos, hogy beágyazható, ezért csak link), `recording` = felvétel link.
 */
async function renderStream(container, media) {
    container.replaceChildren();
    const safe = safeUrl(media.stream);
    const page = safeUrl(media.page);
    const recording = safeUrl(media.recording);

    if (!safe) {
        container.append(el('p', {
            class: 'stream-empty',
            text: page ? 'Az élő adás a stream szolgáltató oldalán nézhető.' : 'Még nincs videó stream.',
        }));
    } else {
        const path = new URL(safe).pathname.toLowerCase();
        if (path.endsWith('.m3u8')) {
            const video = el('video', { controls: '', autoplay: '', playsinline: '' });
            video.muted = true; // autoplay csak némítva engedélyezett
            container.append(video);
            if (video.canPlayType('application/vnd.apple.mpegurl')) {
                video.src = safe;
            } else {
                try {
                    await loadScript(HLS_JS);
                    if (window.Hls && window.Hls.isSupported()) {
                        const hls = new window.Hls();
                        hls.loadSource(safe);
                        hls.attachMedia(video);
                    }
                } catch (e) {
                    console.warn('hls.js betöltése sikertelen', e);
                }
            }
        } else if (/\.(mp4|webm|ogg|mov)$/.test(path)) {
            container.append(el('video', { controls: '', src: safe, playsinline: '' }));
        }
    }

    const links = [
        page && el('a', { href: page, target: '_blank', rel: 'noopener', text: 'Élő adás megnyitása a szolgáltatónál' }),
        safe && el('a', { href: safe, target: '_blank', rel: 'noopener', text: 'Stream megnyitása külön' }),
        recording && el('a', { href: recording, target: '_blank', rel: 'noopener', text: 'Felvétel megtekintése / letöltése' }),
    ].filter(Boolean);
    if (links.length) container.append(el('p', { class: 'stream-link' }, links));
}

/** Egy bejegyzés: {t, type: 'pos'|'msg'|'img', ...}. Ismeretlen vagy hibás bejegyzést kihagyunk. */
function renderEntry(entry) {
    const body = el('div', { class: 'entry-body' });

    if (entry.type === 'pos') {
        const c = coord(entry.lat, entry.lng);
        if (!c) return null;
        body.append(el('span', { class: 'entry-label', text: 'Pozíció' }), mapLink(c));
    } else if (entry.type === 'msg') {
        if (entry.name) body.append(el('span', { class: 'sender', text: String(entry.name) }));
        body.append(el('span', { class: 'text', text: String(entry.text || '') }));
    } else if (entry.type === 'img') {
        const url = safeUrl(entry.url);
        if (!url) return null;
        body.append(el('a', { href: url, target: '_blank', rel: 'noopener' }, [
            el('img', { src: url, alt: 'Kép az eseményről', loading: 'lazy' }),
        ]));
    } else {
        return null;
    }

    const li = el('li', { class: 'entry ' + entry.type });
    if (entry.t) li.append(el('time', { datetime: String(entry.t), text: formatTime(entry.t) }));
    li.append(body);
    return li;
}

function renderBadge(badge, modifiedTime) {
    const modified = Date.parse(modifiedTime);
    const live = isFinite(modified) && Date.now() - modified < LIVE_WINDOW_MS;
    badge.className = 'badge ' + (live ? 'live' : 'idle');
    badge.textContent = live ? 'Élő' : 'Frissítve: ' + formatTime(modifiedTime);
    badge.hidden = !isFinite(modified);
}

export async function initEvent() {
    const box = document.getElementById('event');
    const id = box.dataset.fileId;
    const status = document.getElementById('event-status');
    const title = document.getElementById('event-title');
    const badge = document.getElementById('event-badge');
    const stream = document.getElementById('stream');
    const position = document.getElementById('position');
    const mapBox = document.getElementById('map');
    const timeline = document.getElementById('timeline');
    // Nem production környezetben a `demo-` kezdetű azonosítókat a backend ál-Drive végpontja szolgálja ki
    // (beépített demó adatok, Google nélkül); minden mást a valódi Drive API.
    const demo = Boolean(cfg.demoUrl) && id.startsWith('demo-');
    const fileUrl = demo
        ? new URL(cfg.demoUrl + '/' + encodeURIComponent(id), location.origin).href
        : DRIVE + '/' + encodeURIComponent(id);

    const fail = (text) => {
        box.hidden = true;
        badge.hidden = true;
        status.className = 'status error';
        status.textContent = text;
    };

    if (!demo && !cfg.apiKey) return fail('Az esemény nem érhető el (hiányzó Google API key).');

    let lastModified = null;
    let lastStream;
    let routeMap = null; // Promise<{update}|null> – az első pozíciónál jön létre

    const updateMap = (coords) => {
        if (!coords.length) {
            mapBox.hidden = true;
            return;
        }
        mapBox.hidden = false;
        routeMap = routeMap || createRouteMap(mapBox).catch((e) => {
            console.warn('A térkép nem tölthető be', e);
            mapBox.hidden = true;
            return null;
        });
        routeMap.then((m) => m && m.update(coords));
    };

    async function refresh(force) {
        let meta;
        try {
            meta = await gapi(fileUrl, { fields: 'name,modifiedTime,trashed' });
        } catch (e) {
            if (e.status === 404 || e.status === 403) return fail('Az esemény nem érhető el (törölve vagy nem nyilvános).');
            throw e;
        }
        if (meta.trashed) return fail('Az esemény nem érhető el (törölve vagy nem nyilvános).');
        renderBadge(badge, meta.modifiedTime);
        if (!force && meta.modifiedTime === lastModified) return;

        const data = await gapi(fileUrl, { alt: 'media' });
        lastModified = meta.modifiedTime; // csak sikeres letöltés után, különben a következő körben újrapróbáljuk
        const entries = (data && Array.isArray(data.entries) ? data.entries : [])
            .filter((e) => e && typeof e === 'object');

        const name = displayName(meta.name) || 'Esemény';
        title.textContent = name;
        document.title = name + ' – SOSlive';

        const text = (v) => (typeof v === 'string' ? v : '');
        const media = { stream: text(data && data.stream), page: text(data && data.stream_page), recording: text(data && data.recording) };
        const mediaKey = JSON.stringify(media);
        if (mediaKey !== lastStream) {
            lastStream = mediaKey;
            await renderStream(stream, media);
        }

        const coords = entries.filter((e) => e.type === 'pos').map((e) => coord(e.lat, e.lng)).filter(Boolean);
        position.replaceChildren(...(coords.length
            ? ['Utolsó pozíció: ', mapLink(coords[coords.length - 1])]
            : [el('span', { class: 'muted', text: 'Még nincs pozíció.' })]));

        const atBottom = window.innerHeight + window.scrollY >= document.body.scrollHeight - 50;
        const items = entries.map(renderEntry).filter(Boolean);
        timeline.replaceChildren(...items);
        if (!items.length) timeline.append(el('li', { class: 'empty', text: 'Még nincs bejegyzés.' }));

        status.hidden = true;
        box.hidden = false;
        updateMap(coords); // a box már látható, így a térkép a valódi méretével jön létre
        if (atBottom && !force) window.scrollTo(0, document.body.scrollHeight);
    }

    try {
        await refresh(true);
    } catch (e) {
        status.className = 'status error';
        status.textContent = 'Hiba: ' + e.message;
    }

    // Olcsó polling: csak a fájl modifiedTime-ját nézzük, a tartalmat csak változáskor töltjük le.
    setInterval(() => {
        if (document.hidden) return;
        refresh(false).catch((e) => console.warn('SOSlive frissítés hiba', e));
    }, Math.max(2, cfg.pollSeconds) * 1000);
}
