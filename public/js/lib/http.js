// HTTP: nyilvános Google API (API key), a belépett user (/app/me) és a „be van lépve” jelzés.
import { cfg } from 'soslive/lib/config.js';

export class HttpError extends Error {
    constructor(status, message) {
        super(message || ('HTTP ' + status));
        this.status = status;
    }
}

/** Google API hívás API key-jel, csak olvasás. A demó végpont (nem production) nem kapja meg a kulcsot. */
export async function gapi(url, params) {
    const u = new URL(url);
    Object.entries(params || {}).forEach(([k, v]) => u.searchParams.set(k, v));
    if (u.origin === 'https://www.googleapis.com') u.searchParams.set('key', cfg.apiKey);

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

// Csak jelzés a böngészőben, hogy érdemes-e az /app/me-t hívni ott, ahol nem kötelező (kezdőlap, eseményoldal) –
// így a nyilvános eseményoldal vendég nézői egyáltalán nem terhelik a backendet.
const LOGGED_IN_HINT = 'soslive.loggedIn';

export function storage(action, value) {
    try {
        if (action === 'get') return window.localStorage.getItem(LOGGED_IN_HINT);
        if (action === 'set') window.localStorage.setItem(LOGGED_IN_HINT, value);
        if (action === 'remove') window.localStorage.removeItem(LOGGED_IN_HINT);
    } catch (e) {
        // privát mód / letiltott storage: nincs jelzés
    }
    return null;
}

export async function loadMe() {
    const res = await fetch(cfg.meUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin', cache: 'no-store' });
    if (!res.ok) throw new HttpError(res.status);
    const me = await res.json();
    if (me.user) storage('set', '1');
    else storage('remove');
    return me;
}
