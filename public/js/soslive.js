/*
 * SOSlive böngésző-oldali logika. A web csak olvas: az eseménylistát a backend adja (/events/{owner}),
 * az esemény tartalmát a böngésző közvetlenül a Google API-ból olvassa API key-jel
 * (az esemény fájlok „bárki a linkkel olvashatja” megosztásúak). Formátum: docs/SHEET_FORMAT.md
 */
(function () {
    'use strict';

    const cfgEl = document.getElementById('soslive-config');
    if (!cfgEl) return;
    const cfg = JSON.parse(cfgEl.textContent);

    const DRIVE = 'https://www.googleapis.com/drive/v3/files';
    const SHEETS = 'https://sheets.googleapis.com/v4/spreadsheets';
    const HLS_JS = 'https://cdn.jsdelivr.net/npm/hls.js@1/dist/hls.min.js';

    class HttpError extends Error {
        constructor(status, message) {
            super(message || ('HTTP ' + status));
            this.status = status;
        }
    }

    // ---- Google API (API key, csak olvasás) ---------------------------------------------------

    async function gapi(url, params) {
        const u = new URL(url);
        Object.entries(params || {}).forEach(([k, v]) => u.searchParams.set(k, v));
        u.searchParams.set('key', cfg.apiKey);

        const res = await fetch(u);
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new HttpError(res.status, err.error && err.error.message);
        }
        return res.json();
    }

    // ---- Segédek ------------------------------------------------------------------------------

    function el(tag, attrs, children) {
        const node = document.createElement(tag);
        Object.entries(attrs || {}).forEach(([k, v]) => {
            if (k === 'text') node.textContent = v;
            else node.setAttribute(k, v);
        });
        (children || []).forEach((c) => c && node.append(c));
        return node;
    }

    function safeUrl(value) {
        try {
            const u = new URL(String(value).trim());
            return (u.protocol === 'https:' || u.protocol === 'http:') ? u.href : null;
        } catch (e) {
            return null;
        }
    }

    function formatTime(value) {
        if (!value) return '';
        const d = new Date(value);
        return isNaN(d) ? String(value) : d.toLocaleString('hu-HU');
    }

    function parseCoord(value) {
        const m = String(value || '').match(/^\s*(-?\d+(?:\.\d+)?)\s*[,; ]\s*(-?\d+(?:\.\d+)?)\s*$/);
        if (!m) return null;
        const lat = parseFloat(m[1]);
        const lng = parseFloat(m[2]);
        return (Math.abs(lat) <= 90 && Math.abs(lng) <= 180) ? { lat, lng } : null;
    }

    function mapLink(c) {
        return el('a', {
            href: 'https://www.openstreetmap.org/?mlat=' + c.lat + '&mlon=' + c.lng + '#map=16/' + c.lat + '/' + c.lng,
            target: '_blank',
            rel: 'noopener',
            text: c.lat + ', ' + c.lng,
        });
    }

    // ---- Dashboard ----------------------------------------------------------------------------

    async function loadOwner(owner, section) {
        const status = section.querySelector('.status');
        const list = section.querySelector('.events');

        const res = await fetch(cfg.eventsUrl + '/' + owner.id, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });
        const body = await res.json().catch(() => ({}));

        if (!res.ok) {
            if (body.error === 'reauth') {
                status.replaceChildren('A Google hozzáférés lejárt. ',
                    el('a', { href: '/auth/google?consent=1', text: 'Lépj be újra' }));
            } else if (body.error === 'owner_reauth') {
                status.textContent = 'A tulajdonosnak újra be kell lépnie a SOSlive-ba, addig az eseményei nem érhetők el.';
            } else {
                status.textContent = res.status === 403 ? 'Nincs hozzáférésed.' : 'Hiba történt (' + res.status + ').';
            }
            return;
        }

        if (body.folder_missing) {
            status.textContent = body.is_owner
                ? 'A SOSlive mappa nem található a Drive-odban (törölve?).'
                : 'A tulajdonos SOSlive mappája nem található.';
            if (body.is_owner) section.append(document.getElementById('folder-missing').content.cloneNode(true));
            return;
        }

        status.textContent = body.events.length ? '' : 'Még nincs esemény.';
        list.replaceChildren(...body.events.map((f) => el('li', {}, [
            el('a', { href: '/e/' + encodeURIComponent(f.id), text: f.name || f.id }),
            ' ',
            el('span', { class: 'muted', text: formatTime(f.createdTime) }),
        ])));
    }

    function initDashboard() {
        cfg.owners.forEach((owner) => {
            const section = document.querySelector('.owner[data-owner-id="' + owner.id + '"]');
            if (section) loadOwner(owner, section).catch((e) => {
                section.querySelector('.status').textContent = 'Hiba: ' + e.message;
            });
        });
    }

    // ---- Eseményoldal -------------------------------------------------------------------------

    function loadScript(src) {
        return new Promise((resolve, reject) => {
            const s = el('script', { src });
            s.onload = resolve;
            s.onerror = reject;
            document.head.append(s);
        });
    }

    async function renderStream(container, url) {
        container.replaceChildren();
        const safe = safeUrl(url);
        if (!safe) {
            if (url) container.append(el('p', { class: 'muted', text: 'Stream: ' + url }));
            return;
        }

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
        container.append(el('p', {}, [el('a', { href: safe, target: '_blank', rel: 'noopener', text: 'Stream link' })]));
    }

    function renderRow(row) {
        // row: [A, B idő, C koordináta, D feladó, E üzenet, F képek]
        const [, time, coordRaw, sender, message, images] = row;
        const li = el('li');
        if (time) li.append(el('time', { datetime: time, text: formatTime(time) }));

        const coord = parseCoord(coordRaw);
        if (coord) li.append('📍 ', mapLink(coord), ' ');
        else if (coordRaw) li.append(el('span', { class: 'muted', text: coordRaw + ' ' }));

        if (sender || message) {
            if (sender) li.append(el('span', { class: 'sender', text: sender + ':' }));
            if (message) li.append(el('span', { text: message }));
        }

        String(images || '').split(/[\s,]+/).filter(Boolean).forEach((raw) => {
            const url = safeUrl(raw);
            if (!url) return;
            li.append(el('a', { href: url, target: '_blank', rel: 'noopener' }, [
                el('img', { src: url, alt: 'kép', loading: 'lazy' }),
            ]));
        });
        return li;
    }

    async function initEvent() {
        const id = cfg.spreadsheetId;
        const status = document.getElementById('event-status');
        const box = document.getElementById('event');
        const title = document.getElementById('event-title');
        const stream = document.getElementById('stream');
        const position = document.getElementById('position');
        const timeline = document.getElementById('timeline');
        const fileUrl = DRIVE + '/' + encodeURIComponent(id);

        if (!cfg.apiKey) {
            status.textContent = 'Az esemény nem érhető el (hiányzó Google API key).';
            return;
        }

        let lastModified = null;
        let lastStream;

        const unavailable = () => {
            box.hidden = true;
            status.textContent = 'Az esemény nem érhető el (törölve vagy nem nyilvános).';
        };

        async function refresh(force) {
            let meta;
            try {
                meta = await gapi(fileUrl, { fields: 'name,modifiedTime,trashed' });
            } catch (e) {
                if (e.status === 404 || e.status === 403) return unavailable();
                throw e;
            }
            if (meta.trashed) return unavailable();
            if (!force && meta.modifiedTime === lastModified) return;
            lastModified = meta.modifiedTime;

            const data = await gapi(SHEETS + '/' + encodeURIComponent(id) + '/values/' + encodeURIComponent('A1:F'));
            const rows = data.values || [];

            title.textContent = meta.name || 'Esemény';
            document.title = (meta.name || 'Esemény') + ' – SOSlive';

            const streamUrl = (rows[0] && rows[0][0]) || '';
            if (streamUrl !== lastStream) {
                lastStream = streamUrl;
                await renderStream(stream, streamUrl);
            }

            const body = rows.slice(1).filter((r) => r.slice(1).some(Boolean));
            const coords = body.map((r) => parseCoord(r[2])).filter(Boolean);
            position.replaceChildren();
            if (coords.length) position.append('Utolsó pozíció: ', mapLink(coords[coords.length - 1]));

            const atBottom = window.innerHeight + window.scrollY >= document.body.scrollHeight - 50;
            timeline.replaceChildren(...body.map(renderRow));
            if (!body.length) timeline.append(el('li', { class: 'muted', text: 'Még nincs bejegyzés.' }));

            status.textContent = '';
            box.hidden = false;
            if (atBottom && !force) window.scrollTo(0, document.body.scrollHeight);
        }

        try {
            await refresh(true);
        } catch (e) {
            status.textContent = 'Hiba: ' + e.message;
        }

        // Olcsó polling: csak a Drive modifiedTime-ot nézzük, a Sheets értékeket csak változáskor kérjük le.
        setInterval(() => {
            if (document.hidden) return;
            refresh(false).catch((e) => console.warn('SOSlive frissítés hiba', e));
        }, Math.max(2, cfg.pollSeconds) * 1000);
    }

    if (cfg.page === 'dashboard') initDashboard();
    if (cfg.page === 'event') initEvent();
})();
