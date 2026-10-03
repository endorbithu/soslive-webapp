// A user saját Drive-ja (Google Identity Services, drive.file, a user saját tokenje). A backend nem kezel Google tokent.
import { cfg, DRIVE, FOLDER_MIME, GIS_JS } from 'soslive/lib/config.js';
import { loadScript } from 'soslive/lib/dom.js';
import { HttpError } from 'soslive/lib/http.js';

export class TokenError extends Error {}

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

export function storeToken(t) {
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
export async function findFolder(me) {
    const res = await drive(me, DRIVE, { params: {
        q: query(["appProperties has { key='soslive' and value='root' }", "mimeType='" + FOLDER_MIME + "'", 'trashed=false']),
        orderBy: 'createdTime',
        pageSize: 1,
        fields: 'files(id)',
    } });
    return (res.files && res.files[0]) ? res.files[0].id : null;
}

export async function createFolder(me) {
    const res = await drive(me, DRIVE, {
        method: 'POST',
        params: { fields: 'id' },
        body: { name: cfg.folderName, mimeType: FOLDER_MIME, appProperties: { soslive: 'root' } },
    });
    return res.id;
}

/** A mobil app által írt config.json tartalma, vagy null ha még nincs. */
export async function readConfig(me, folderId) {
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

export async function listEvents(me, folderId, limit) {
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
export async function withDrive(me, run) {
    const status = document.getElementById('page-status');
    const access = document.getElementById('drive-access');
    const attempt = async () => {
        access.hidden = true;
        status.hidden = false;
        status.className = 'status';
        status.textContent = 'Betöltés…';
        try {
            await run();
        } catch (e) {
            if (!(e instanceof TokenError)) {
                status.className = 'status error';
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
