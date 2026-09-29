/*
 * SOSlive böngésző-oldali logika.
 * - Az oldalak HTML-je statikus és reverse proxyban cache-elődik; minden userfüggő adat (belépett user, CSRF token,
 *   tulajok, config) az /app/me JSON-ból jön, az eseménylista az /app/events/{owner}-ből.
 * - Az esemény tartalmát a böngésző közvetlenül a Google Drive API-ból olvassa API key-jel
 *   (az esemény JSON fájlok „bárki a linkkel olvashatja” megosztásúak). Formátum: docs/EVENT_FORMAT.md
 */
(function () {
    'use strict';

    const cfgEl = document.getElementById('soslive-config');
    if (!cfgEl) return;
    const cfg = JSON.parse(cfgEl.textContent);

    const DRIVE = 'https://www.googleapis.com/drive/v3/files';
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
        logout.addEventListener('submit', () => storage('remove'));
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

    // ---- Dashboard ----------------------------------------------------------------------------

    async function loadOwner(owner, section, me) {
        const status = section.querySelector('.status');
        const list = section.querySelector('.events');

        const res = await fetch(cfg.eventsUrl + '/' + owner.id, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
            cache: 'no-store',
        });
        const body = await res.json().catch(() => ({}));

        if (!res.ok) {
            if (body.error === 'reauth') {
                status.replaceChildren('A Google hozzáférés lejárt. ',
                    el('a', { href: cfg.loginUrl + '?consent=1', text: 'Lépj be újra' }));
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
            if (body.is_owner) section.append(csrfForm(cfg.folderUrl, me, 'SOSlive mappa létrehozása'));
            return;
        }

        status.textContent = body.events.length ? '' : 'Még nincs esemény.';
        list.replaceChildren(...body.events.map((f) => el('li', {}, [
            el('a', { href: '/e/' + encodeURIComponent(f.id), text: displayName(f.name) || f.id }),
            ' ',
            el('span', { class: 'muted', text: formatTime(f.createdTime) }),
        ])));
    }

    function initDashboard(me) {
        const status = document.getElementById('page-status');
        if (!me.user) return loginPrompt(status, 'Az események megtekintéséhez lépj be.');
        status.hidden = true;

        const container = document.getElementById('owners');
        me.owners.forEach((owner) => {
            const section = el('section', { class: 'owner' }, [
                el('h2', { text: owner.is_me ? 'Saját eseményeim' : owner.name + ' (' + owner.email + ')' }),
                el('p', { class: 'status muted', text: 'Betöltés…' }),
                el('ul', { class: 'events' }),
            ]);
            container.append(section);
            loadOwner(owner, section, me).catch((e) => {
                section.querySelector('.status').textContent = 'Hiba: ' + e.message;
            });
        });
    }

    // ---- Beállítások (csak olvasható; módosítani a mobil appban lehet) --------------------------

    function initSettings(me) {
        const status = document.getElementById('page-status');
        if (!me.user) return loginPrompt(status, 'A beállítások megtekintéséhez lépj be.');
        status.hidden = true;

        const config = me.config;
        const list = (id, items) => {
            const dd = document.getElementById(id);
            dd.replaceChildren();
            if (!items.length) dd.textContent = '–';
            items.forEach((item, i) => dd.append(...(i ? [el('br'), item] : [item])));
        };
        list('cfg-notification-emails', config.notification_emails);
        list('cfg-notification-phones', config.notification_phones);
        list('cfg-allowed-emails', config.allowed_emails);
        document.getElementById('cfg-max-events').textContent = config.max_events;

        if (config.drive_folder_id) {
            const link = document.getElementById('cfg-folder-link');
            link.href = 'https://drive.google.com/drive/folders/' + encodeURIComponent(config.drive_folder_id);
            link.hidden = false;
        }
        document.querySelectorAll('form.csrf-form').forEach((form) => {
            form.prepend(el('input', { type: 'hidden', name: '_token', value: me.csrf }));
        });
        document.getElementById('settings').hidden = false;
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
