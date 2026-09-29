/*
 * SOSlive böngésző-oldali logika. Az esemény-adatok a Google Drive / Sheets API-n keresztül,
 * közvetlenül a böngészőből jönnek-mennek; a backend csak access tokent ad (/token/{owner}).
 * Formátum: docs/SHEET_FORMAT.md
 */
(function () {
    'use strict';

    const cfgEl = document.getElementById('soslive-config');
    if (!cfgEl) return;
    const cfg = JSON.parse(cfgEl.textContent);

    const DRIVE = 'https://www.googleapis.com/drive/v3/files';
    const SHEETS = 'https://sheets.googleapis.com/v4/spreadsheets';
    const SPREADSHEET_MIME = 'application/vnd.google-apps.spreadsheet';
    const HLS_JS = 'https://cdn.jsdelivr.net/npm/hls.js@1/dist/hls.min.js';

    class HttpError extends Error {
        constructor(status, code, message) {
            super(message || ('HTTP ' + status));
            this.status = status;
            this.code = code;
        }
    }

    // ---- Tokenek ------------------------------------------------------------------------------

    const tokens = {};

    async function ownerToken(ownerId, force) {
        const t = tokens[ownerId];
        if (!force && t && t.expires_at * 1000 - Date.now() > 60000) return t;

        const res = await fetch(cfg.tokenUrl + '/' + ownerId, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });
        const body = await res.json().catch(() => ({}));
        if (!res.ok) throw new HttpError(res.status, body.error);
        tokens[ownerId] = body;
        return body;
    }

    // ---- Google API ---------------------------------------------------------------------------

    /**
     * auth: {ownerId} a tulaj tokenjével, vagy null → API key (csak publikus fájlokhoz).
     */
    async function gapi(url, opts) {
        opts = opts || {};
        const auth = opts.auth || null;

        for (let attempt = 0; attempt < 2; attempt++) {
            const u = new URL(url);
            Object.entries(opts.params || {}).forEach(([k, v]) => {
                if (v !== undefined && v !== null) u.searchParams.set(k, v);
            });
            const headers = {};
            if (auth) {
                headers.Authorization = 'Bearer ' + (await ownerToken(auth.ownerId, attempt > 0)).access_token;
            } else if (cfg.apiKey) {
                u.searchParams.set('key', cfg.apiKey);
            }
            if (opts.body) headers['Content-Type'] = 'application/json';

            const res = await fetch(u, {
                method: opts.method || 'GET',
                headers,
                body: opts.body ? JSON.stringify(opts.body) : undefined,
            });
            if (res.status === 401 && auth && attempt === 0) continue; // lejárt token → újat kérünk
            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                throw new HttpError(res.status, null, err.error && err.error.message);
            }
            return res.status === 204 ? null : res.json();
        }
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

    function tokenErrorText(e) {
        if (e.code === 'reauth') return null; // külön kezeljük: újra-belépés link
        if (e.code === 'owner_reauth') return 'A tulajdonosnak újra be kell lépnie a SOSlive-ba, addig az eseményei nem érhetők el.';
        if (e.status === 403) return 'Nincs hozzáférésed.';
        return 'Hiba történt (' + (e.message || e.status) + ').';
    }

    // ---- Dashboard ----------------------------------------------------------------------------

    function eventQuery(folderId) {
        return "'" + folderId + "' in parents and trashed=false and mimeType='" + SPREADSHEET_MIME + "'";
    }

    async function loadOwner(owner, section) {
        const status = section.querySelector('.status');
        const list = section.querySelector('.events');
        const auth = { ownerId: owner.id };

        let token;
        try {
            token = await ownerToken(owner.id);
        } catch (e) {
            if (e.code === 'reauth') {
                status.replaceChildren('A Google hozzáférés lejárt. ',
                    el('a', { href: '/auth/google?consent=1', text: 'Lépj be újra' }));
            } else {
                status.textContent = tokenErrorText(e);
            }
            return;
        }

        const folderMissing = () => {
            status.textContent = token.is_owner
                ? 'A SOSlive mappa nem található a Drive-odban (törölve?).'
                : 'A tulajdonos SOSlive mappája nem található.';
            if (token.is_owner) {
                section.append(document.getElementById('folder-missing').content.cloneNode(true));
            }
        };

        if (!token.folder_id) return folderMissing();
        try {
            const folder = await gapi(DRIVE + '/' + encodeURIComponent(token.folder_id), { auth, params: { fields: 'id,trashed' } });
            if (folder.trashed) return folderMissing();
        } catch (e) {
            if (e.status === 404) return folderMissing();
            status.textContent = 'Hiba: ' + e.message;
            return;
        }

        const pageSize = Math.min(cfg.listLimit, token.max_events);
        let files;
        try {
            files = (await gapi(DRIVE, {
                auth,
                params: {
                    q: eventQuery(token.folder_id),
                    orderBy: 'createdTime desc',
                    pageSize,
                    fields: 'files(id,name,createdTime)',
                },
            })).files || [];
        } catch (e) {
            status.textContent = 'Hiba: ' + e.message;
            return;
        }

        status.textContent = files.length ? '' : 'Még nincs esemény.';
        list.replaceChildren(...files.map((f) => el('li', {}, [
            el('a', { href: '/e/' + encodeURIComponent(f.id), text: f.name || f.id }),
            ' ',
            el('span', { class: 'muted', text: formatTime(f.createdTime) }),
        ])));

        if (token.is_owner && files.length >= Math.min(pageSize, token.max_events)) {
            rotate(auth, token).catch((e) => console.warn('SOSlive rotáció hiba', e));
        }
    }

    /**
     * max_events feletti (legrégebbi) esemény-fájlok kukába helyezése. Csak az appProperties-szel
     * eseményként megjelölt fájlokhoz nyúl.
     */
    async function rotate(auth, token) {
        const q = eventQuery(token.folder_id) + " and appProperties has { key='soslive' and value='event' }";
        const ids = [];
        let pageToken;
        do {
            const res = await gapi(DRIVE, {
                auth,
                params: { q, orderBy: 'createdTime desc', pageSize: 1000, fields: 'nextPageToken,files(id)', pageToken },
            });
            (res.files || []).forEach((f) => ids.push(f.id));
            pageToken = res.nextPageToken;
        } while (pageToken);

        for (const id of ids.slice(token.max_events)) {
            await gapi(DRIVE + '/' + encodeURIComponent(id), { auth, method: 'PATCH', body: { trashed: true } });
        }
    }

    function initDashboard() {
        cfg.owners.forEach((owner) => {
            const section = document.querySelector('.owner[data-owner-id="' + owner.id + '"]');
            if (section) loadOwner(owner, section);
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
        const chat = document.getElementById('chat');
        const fileUrl = DRIVE + '/' + encodeURIComponent(id);

        // Bejelentkezve: megkeressük, melyik (általunk látható) tulaj fájlja ez → írási jog a chathez.
        let auth = null;
        for (const owner of cfg.owners || []) {
            try {
                await gapi(fileUrl, { auth: { ownerId: owner.id }, params: { fields: 'id' } });
                auth = { ownerId: owner.id };
                break;
            } catch (e) {
                // nem ennek a tulajnak a fájlja, vagy nem elérhető – megyünk tovább
            }
        }
        if (!auth && !cfg.apiKey) {
            status.textContent = 'Az esemény nem érhető el.';
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
                meta = await gapi(fileUrl, { auth, params: { fields: 'name,modifiedTime,trashed' } });
            } catch (e) {
                if (e.status === 404 || e.status === 403) return unavailable();
                throw e;
            }
            if (meta.trashed) return unavailable();
            if (!force && meta.modifiedTime === lastModified) return;
            lastModified = meta.modifiedTime;

            const data = await gapi(SHEETS + '/' + encodeURIComponent(id) + '/values/' + encodeURIComponent('A1:F'), { auth });
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

            status.textContent = auth ? '' : 'Csak olvasható nézet.';
            box.hidden = false;
            if (atBottom && !force) window.scrollTo(0, document.body.scrollHeight);
        }

        if (auth && cfg.me) {
            chat.hidden = false;
            chat.addEventListener('submit', async (ev) => {
                ev.preventDefault();
                const input = chat.elements.message;
                const text = input.value.trim();
                if (!text) return;
                const button = chat.querySelector('button');
                button.disabled = true;
                try {
                    // RAW: a beírt szöveg sosem értelmeződik képletként.
                    await gapi(SHEETS + '/' + encodeURIComponent(id) + '/values/' + encodeURIComponent('A:F') + ':append', {
                        auth,
                        method: 'POST',
                        params: { valueInputOption: 'RAW', insertDataOption: 'INSERT_ROWS' },
                        body: { values: [['', new Date().toISOString(), '', cfg.me.name, text, '']] },
                    });
                    input.value = '';
                    await refresh(true);
                    window.scrollTo(0, document.body.scrollHeight);
                } catch (e) {
                    alert('Az üzenet elküldése nem sikerült: ' + e.message);
                } finally {
                    button.disabled = false;
                }
            });
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
