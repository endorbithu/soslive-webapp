// Közös felület: navigáció a belépett usernek, üzenetek (?msg=kód), belépésre felszólítás.
import { cfg } from 'soslive/lib/config.js';
import { el } from 'soslive/lib/dom.js';
import { storage } from 'soslive/lib/http.js';
import { storeToken } from 'soslive/lib/drive.js';

function csrfForm(action, me, buttonText) {
    return el('form', { method: 'post', action, class: 'inline' }, [
        el('input', { type: 'hidden', name: '_token', value: me.csrf }),
        el('button', { type: 'submit', class: 'link', text: buttonText }),
    ]);
}

export function renderNav(me) {
    const nav = document.getElementById('nav');
    if (!nav || !me.user) return;

    const logout = csrfForm(cfg.logoutUrl, me, 'Kilépés');
    logout.addEventListener('submit', () => {
        storage('remove');
        storeToken(null);
    });
    const here = window.location.pathname;
    const link = (href, text) => el('a', here === href ? { href, text, 'aria-current': 'page' } : { href, text });
    nav.replaceChildren(
        link('/dashboard', 'Események'),
        link('/settings', 'Beállítások'),
        el('span', { class: 'nav-user', text: me.user.email }),
        logout,
    );
}

// Csak a fix szövegtárból írunk ki, az URL-ből semmit.
const MESSAGES = {
    login_failed: ['err', 'A Google bejelentkezés nem sikerült.'],
    drive_scope: ['err', 'A működéshez engedélyezni kell a Google Drive hozzáférést (csak az app által létrehozott fájlok).'],
    folder_error: ['err', 'A SOSlive mappát nem sikerült elérni / létrehozni a Drive-on. Próbáld újra a Beállításokban.'],
    folder_ok: ['ok', 'A SOSlive mappa rendben van.'],
};

export function showFlash() {
    const flash = document.getElementById('flash');
    const msg = MESSAGES[new URLSearchParams(window.location.search).get('msg')];
    if (!flash || !msg) return;
    flash.className = 'flash ' + msg[0];
    flash.textContent = msg[1];
    flash.hidden = false;
}

export function loginPrompt(status, text) {
    status.className = 'status empty';
    status.replaceChildren(
        el('span', { text }),
        el('a', { class: 'button', href: cfg.loginUrl, text: 'Belépés Google-fiókkal' }),
    );
}
