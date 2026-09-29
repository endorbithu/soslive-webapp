/*
 * SOSlive böngésző-oldali logika.
 * - Az oldalak HTML-je statikus és reverse proxyban cache-elődik; a belépett user és a CSRF token az /app/me-ből jön.
 * - A saját eseménylistát és a config.json-t a böngésző a user saját Google tokenjével (Google Identity Services,
 *   drive.file) olvassa közvetlenül a Drive-ból – a backend nem kezel Google tokent és nem hív Google API-t.
 * - Egy esemény tartalmát (nyilvános oldal) API key-jel olvassa; az esemény fájlok „bárki a linkkel olvashatja” megosztásúak.
 * Formátum: docs/EVENT_FORMAT.md
 */
(function () {
    'use strict';

    const cfgEl = document.getElementById('soslive-config');
    if (!cfgEl) return;
    const cfg = JSON.parse(cfgEl.textContent);

    const DRIVE = 'https://www.googleapis.com/drive/v3/files';
    const HLS_JS = 'https://cdn.jsdelivr.net/npm/hls.js@1/dist/hls.min.js';
    const GIS_JS = 'https://accounts.google.com/gsi/client';
    const FOLDER_MIME = 'application/vnd.google-apps.folder';

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

        const res = await fetch(u, { cache: 'no-store' });
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new HttpError(res.status, err.error && err.error.message);
        }
        return res.text().then((text) => {
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new HttpError(res.status, 'Érvénytelen JSON');
            }
        });
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

    function coord(lat, lng) {
        lat = Number(lat);
        lng = Number(lng);
        return (isFinite(lat) && isFinite(lng) && Math.abs(lat) <= 90 && Math.abs(lng) <= 180) ? { lat, lng } : null;
    }

    function displayName(fileName) {
        return String(fileName || '').replace(/\.json$/i, '');
    }

    function mapLink(c) {
        return el('a', {
            href: 'https://www.openstreetmap.org/?mlat=' + c.lat + '&mlon=' + c.lng + '#map=16/' + c.lat + '/' + c.lng,
            target: '_blank',
            rel: 'noopener',
            text: c.lat + ', ' + c.lng,
        });
    }

    // ---- Belépett user (/app/me), navigáció, üzenetek ------------------------------------------

    // Csak jelzés a böngészőben, hogy érdemes-e az /app/me-t hívni ott, ahol nem kötelező (kezdőlap, eseményoldal) –
    // így a nyilvános eseményoldal vendég nézői egyáltalán nem terhelik a backendet.
    const LOGGED_IN_HINT = 'soslive.loggedIn';

    function storage(action, value) {
        try {
            if (action === 'get') return window.localStorage.getItem(LOGGED_IN_HINT);
            if (action === 'set') window.localStorage.setItem(LOGGED_IN_HINT, value);
            if (action === 'remove') window.localStorage.removeItem(LOGGED_IN_HINT);
        } catch (e) {
            // privát mód / letiltott storage: nincs jelzés
        }
        return null;
    }

    async function loadMe() {
        const res = await fetch(cfg.meUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin', cache: 'no-store' });
        if (!res.ok) throw new HttpError(res.status);
        const me = await res.json();
        if (me.user) storage('set', '1');
        else storage('remove');
        return me;
    }

    function csrfForm(action, me, buttonText, className) {
        return el('form', { method: 'post', action, class: className || '' }, [
            el('input', { type: 'hidden', name: '_token', value: me.csrf }),
            el('button', { type: 'submit', class: className === 'inline' ? 'link' : '', text: buttonText }),
        ]);
    }

    function renderNav(me) {
        const nav = document.getElementById('nav');
        if (!nav || !me.user) return;

        const logout = csrfForm(cfg.logoutUrl, me, 'Kilépés', 'inline');
        logout.addEventListener('submit', () => {
            storage('remove');
            storeToken(null);
        });
        nav.replaceChildren(
            el('a', { href: '/dashboard', text: 'Események' }),
            el('a', { href: '/settings', text: 'Beállítások' }),
            el('span', { class: 'muted', text: me.user.email }),
            logout,
        );
    }

    // Üzenetek query paraméterből (?msg=kód); csak a fix szövegtárból írunk ki, az URL-ből semmit.
    const MESSAGES = {
        login_failed: ['err', 'A Google bejelentkezés nem sikerült.'],
        drive_scope: ['err', 'A működéshez engedélyezni kell a Google Drive hozzáférést (csak az app által létrehozott fájlok).'],
        folder_error: ['err', 'A SOSlive mappát nem sikerült elérni / létrehozni a Drive-on. Próbáld újra a Beállításokban.'],
        folder_ok: ['ok', 'A SOSlive mappa rendben van.'],
    };

    function showFlash() {
        const flash = document.getElementById('flash');
        const msg = MESSAGES[new URLSearchParams(window.location.search).get('msg')];
        if (!flash || !msg) return;
        flash.className = 'flash ' + msg[0];
        flash.textContent = msg[1];
        flash.hidden = false;
    }

    function loginPrompt(status, text) {
        status.replaceChildren(text + ' ', el('a', { href: cfg.loginUrl, text: 'Belépés Google-fiókkal' }));
    }

    // ---- Saját Drive hozzáférés (Google Identity Services, a user saját tokenje) ------------------

    class TokenError extends Error {}

    // { value, expiresAt } – a lejáratáig (max. 1 óra) a fül sessionStorage-ában is megmarad, hogy oldalváltáskor ne
    // kelljen új Google ablak (a böngészők a nem kattintásra nyíló ablakot letiltják). Kilépéskor törlődik.
    const TOKEN_KEY = 'soslive.driveToken';
    let driveToken = readStoredToken();

    function readStoredToken() {
        try {
            const t = JSON.parse(window.sessionStorage.getItem(TOKEN_KEY));
            return t && t.value && t.expiresAt > Date.now() + 60000 ? t : null;
        } catch (e) {
            return null;
        }
    }

    function storeToken(t) {
        driveToken = t;
        try {
            if (t) window.sessionStorage.setItem(TOKEN_KEY, JSON.stringify(t));
            else window.sessionStorage.removeItem(TOKEN_KEY);
        } catch (e) {
            // letiltott storage: marad a memóriában
        }
    }

    async function requestDriveToken(me) {
        await loadScript(GIS_JS);
        return new Promise((resolve, reject) => {
            const client = window.google.accounts.oauth2.initTokenClient({
                client_id: cfg.googleClientId,
                scope: cfg.driveScope,
                login_hint: me.user.email,
                callback: (resp) => {
                    if (resp.error) return reject(new TokenError(resp.error));
                    storeToken({ value: resp.access_token, expiresAt: Date.now() + (Number(resp.expires_in) || 3600) * 1000 });
                    resolve(driveToken.value);
                },
                // pl. popup_failed_to_open (nem felhasználói kattintásra indult), popup_closed
                error_callback: (err) => reject(new TokenError(err && err.type || 'token_error')),
            });
            // Belépéskor a drive.file engedélyt már megadta, így consent képernyő nélkül kap tokent.
            client.requestAccessToken({ prompt: '' });
        });
    }

    async function drive(me, url, opts) {
        opts = opts || {};
        for (let attempt = 0; attempt < 2; attempt++) {
            if (!driveToken || driveToken.expiresAt - Date.now() < 60000) await requestDriveToken(me);
            const u = new URL(url);
            Object.entries(opts.params || {}).forEach(([k, v]) => u.searchParams.set(k, v));
            const headers = { Authorization: 'Bearer ' + driveToken.value };
            if (opts.body) headers['Content-Type'] = 'application/json';

            const res = await fetch(u, {
                method: opts.method || 'GET',
                headers,
                body: opts.body ? JSON.stringify(opts.body) : undefined,
                cache: 'no-store',
            });
            if (res.status === 401 && attempt === 0) {
                storeToken(null); // lejárt / visszavont token → újat kérünk
                continue;
            }
            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                throw new HttpError(res.status, err.error && err.error.message);
            }
            return res.json();
        }
    }

    function query(parts) {
        return parts.join(' and ');
    }

    /** A user SOSlive mappája (több találatnál a legrégebbi), vagy null. */
    async function findFolder(me) {
        const res = await drive(me, DRIVE, { params: {
            q: query(["appProperties has { key='soslive' and value='root' }", "mimeType='" + FOLDER_MIME + "'", 'trashed=false']),
            orderBy: 'createdTime',
            pageSize: 1,
            fields: 'files(id)',
        } });
        return (res.files && res.files[0]) ? res.files[0].id : null;
    }

    async function createFolder(me) {
        const res = await drive(me, DRIVE, {
            method: 'POST',
            params: { fields: 'id' },
            body: { name: cfg.folderName, mimeType: FOLDER_MIME, appProperties: { soslive: 'root' } },
        });
        return res.id;
    }

    /** A mobil app által írt config.json tartalma, vagy null ha még nincs. */
    async function readConfig(me, folderId) {
        const res = await drive(me, DRIVE, { params: {
            q: query(["'" + folderId + "' in parents", "appProperties has { key='soslive' and value='config' }", 'trashed=false']),
            orderBy: 'modifiedTime desc',
            pageSize: 1,
            fields: 'files(id)',
        } });
        if (!res.files || !res.files.length) return null;

        const data = await drive(me, DRIVE + '/' + encodeURIComponent(res.files[0].id), { params: { alt: 'media' } });
        const list = (v) => (Array.isArray(v) ? v.filter((x) => typeof x === 'string') : []);
        const max = parseInt(data && data.max_events, 10);
        return {
            notification_emails: list(data && data.notification_emails),
            notification_phones: list(data && data.notification_phones),
            max_events: max > 0 ? max : cfg.defaultMaxEvents,
        };
    }

    async function listEvents(me, folderId, limit) {
        const res = await drive(me, DRIVE, { params: {
            q: query(["'" + folderId + "' in parents", "appProperties has { key='soslive' and value='event' }", 'trashed=false']),
            orderBy: 'createdTime desc',
            pageSize: limit,
            fields: 'files(id,name,createdTime)',
        } });
        return res.files || [];
    }

    /**
     * Drive tokent kér, és lefuttatja a betöltést. Ha a token nem kérhető automatikusan (pl. a böngésző letiltja a
     * Google ablakot), gombot mutat: kattintásra (felhasználói művelet) már megnyílhat.
     */
    async function withDrive(me, run) {
        const status = document.getElementById('page-status');
        const access = document.getElementById('drive-access');
        const attempt = async () => {
            access.hidden = true;
            status.hidden = false;
            status.textContent = 'Betöltés…';
            try {
                await run();
            } catch (e) {
                if (!(e instanceof TokenError)) {
                    status.textContent = 'Hiba: ' + e.message;
                    return;
                }
                status.textContent = 'A Google Drive-od eléréséhez engedélyezd a hozzáférést.';
                access.hidden = false;
            }
        };
        access.querySelector('button').onclick = attempt;
        await attempt();
    }

    // ---- Dashboard: saját eseménylista --------------------------------------------------------

    function initDashboard(me) {
        const status = document.getElementById('page-status');
        if (!me.user) return loginPrompt(status, 'Az események megtekintéséhez lépj be.');

        const list = document.getElementById('events');
        const load = async () => {
            const folderId = await findFolder(me);
            if (!folderId) {
                const button = el('button', { type: 'button', text: 'SOSlive mappa létrehozása' });
                button.addEventListener('click', () => withDrive(me, async () => {
                    await createFolder(me);
                    await load();
                }));
                status.replaceChildren('Még nincs SOSlive mappa a Drive-odban (a mobil app az első eseménynél létrehozza). ', button);
                return;
            }

            const config = await readConfig(me, folderId);
            const maxEvents = config ? config.max_events : cfg.defaultMaxEvents;
            const events = await listEvents(me, folderId, Math.min(cfg.listLimit, maxEvents));

            status.textContent = events.length ? '' : 'Még nincs esemény.';
            status.hidden = !!events.length;
            list.replaceChildren(...events.map((f) => el('li', {}, [
                el('a', { href: '/e/' + encodeURIComponent(f.id), text: displayName(f.name) || f.id }),
                ' ',
                el('span', { class: 'muted', text: formatTime(f.createdTime) }),
            ])));
        };
        withDrive(me, load);
    }

    // ---- Beállítások: a mobil app által írt config.json, csak olvasható ------------------------

    function initSettings(me) {
        const status = document.getElementById('page-status');
        if (!me.user) return loginPrompt(status, 'A beállítások megtekintéséhez lépj be.');

        const fill = (id, items) => {
            const dd = document.getElementById(id);
            dd.replaceChildren();
            if (!items.length) dd.textContent = '–';
            items.forEach((item, i) => dd.append(...(i ? [el('br'), item] : [item])));
        };

        withDrive(me, async () => {
            const folderId = await findFolder(me);
            const config = folderId ? await readConfig(me, folderId) : null;

            document.getElementById('cfg-missing').hidden = !!config;
            fill('cfg-notification-emails', config ? config.notification_emails : []);
            fill('cfg-notification-phones', config ? config.notification_phones : []);
            document.getElementById('cfg-max-events').textContent = config ? config.max_events : cfg.defaultMaxEvents;

            const link = document.getElementById('cfg-folder-link');
            link.hidden = !folderId;
            if (folderId) link.href = 'https://drive.google.com/drive/folders/' + encodeURIComponent(folderId);

            status.hidden = true;
            document.getElementById('settings').hidden = false;
        });
    }

    // ---- Kezdőlap -----------------------------------------------------------------------------

    function initHome(me) {
        const cta = document.getElementById('home-cta');
        if (cta && me.user) {
            cta.href = '/dashboard';
            cta.textContent = 'Eseményeim';
        }
    }

    // ---- Eseményoldal -------------------------------------------------------------------------

    const scripts = {};

    function loadScript(src) {
        scripts[src] = scripts[src] || new Promise((resolve, reject) => {
            const s = el('script', { src });
            s.onload = resolve;
            s.onerror = () => {
                delete scripts[src];
                reject(new Error('Nem tölthető be: ' + src));
            };
            document.head.append(s);
        });
        return scripts[src];
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

    /**
     * Egy bejegyzés: {t, type: 'pos'|'msg'|'img', ...}. Ismeretlen típust kihagyunk.
     */
    function renderEntry(entry) {
        const li = el('li');
        if (entry.t) li.append(el('time', { datetime: String(entry.t), text: formatTime(entry.t) }));

        if (entry.type === 'pos') {
            const c = coord(entry.lat, entry.lng);
            if (!c) return null;
            li.append('📍 ', mapLink(c));
        } else if (entry.type === 'msg') {
            if (entry.name) li.append(el('span', { class: 'sender', text: String(entry.name) + ':' }));
            li.append(el('span', { text: String(entry.text || '') }));
        } else if (entry.type === 'img') {
            const url = safeUrl(entry.url);
            if (!url) return null;
            li.append(el('a', { href: url, target: '_blank', rel: 'noopener' }, [
                el('img', { src: url, alt: 'kép', loading: 'lazy' }),
            ]));
        } else {
            return null;
        }
        return li;
    }

    async function initEvent() {
        const id = document.getElementById('event').dataset.fileId;
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

            const data = await gapi(fileUrl, { alt: 'media' });
            lastModified = meta.modifiedTime; // csak sikeres letöltés után, különben a következő körben újrapróbáljuk
            const entries = (data && Array.isArray(data.entries) ? data.entries : [])
                .filter((e) => e && typeof e === 'object');

            const name = displayName(meta.name) || 'Esemény';
            title.textContent = name;
            document.title = name + ' – SOSlive';

            const streamUrl = (data && typeof data.stream === 'string') ? data.stream : '';
            if (streamUrl !== lastStream) {
                lastStream = streamUrl;
                await renderStream(stream, streamUrl);
            }

            const coords = entries.filter((e) => e.type === 'pos').map((e) => coord(e.lat, e.lng)).filter(Boolean);
            position.replaceChildren();
            if (coords.length) position.append('Utolsó pozíció: ', mapLink(coords[coords.length - 1]));

            const atBottom = window.innerHeight + window.scrollY >= document.body.scrollHeight - 50;
            const items = entries.map(renderEntry).filter(Boolean);
            timeline.replaceChildren(...items);
            if (!items.length) timeline.append(el('li', { class: 'muted', text: 'Még nincs bejegyzés.' }));

            status.textContent = '';
            box.hidden = false;
            if (atBottom && !force) window.scrollTo(0, document.body.scrollHeight);
        }

        try {
            await refresh(true);
        } catch (e) {
            status.textContent = 'Hiba: ' + e.message;
        }

        // Olcsó polling: csak a fájl modifiedTime-ját nézzük, a tartalmat csak változáskor töltjük le.
        setInterval(() => {
            if (document.hidden) return;
            refresh(false).catch((e) => console.warn('SOSlive frissítés hiba', e));
        }, Math.max(2, cfg.pollSeconds) * 1000);
    }

    // ---- Indulás ------------------------------------------------------------------------------

    async function init() {
        showFlash();
        if (cfg.page === 'event') initEvent();

        // A dashboard és a beállítások mindig kéri a usert; a többi oldal csak akkor, ha a böngésző szerint be van lépve.
        const needsMe = cfg.page === 'dashboard' || cfg.page === 'settings';
        if (!needsMe && !storage('get')) return;

        let me;
        try {
            me = await loadMe();
        } catch (e) {
            const status = document.getElementById('page-status');
            if (status) status.textContent = 'Hiba: ' + e.message;
            return;
        }
        renderNav(me);
        if (cfg.page === 'dashboard') initDashboard(me);
        if (cfg.page === 'settings') initSettings(me);
        if (cfg.page === 'home') initHome(me);
    }

    init();
})();
