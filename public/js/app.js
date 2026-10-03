/*
 * SOSlive böngésző-oldali logika (natív ES modulok, build lépés nélkül; verziózás: import map a
 * resources/views/partials/config.blade.php-ben).
 * - Az oldalak HTML-je statikus és reverse proxyban cache-elődik; a belépett user és a CSRF token az /app/me-ből jön.
 * - A saját eseménylistát és a config.json-t a böngésző a user saját Google tokenjével (Google Identity Services,
 *   drive.file) olvassa közvetlenül a Drive-ból – a backend nem kezel Google tokent és nem hív Google API-t.
 * - Egy esemény tartalmát (nyilvános oldal) API key-jel olvassa; az esemény fájlok „bárki a linkkel olvashatja” megosztásúak.
 * Formátum: docs/EVENT_FORMAT.md
 */
import { cfg } from 'soslive/lib/config.js';
import { loadMe, storage } from 'soslive/lib/http.js';
import { renderNav, showFlash } from 'soslive/lib/ui.js';
import { initDashboard } from 'soslive/pages/dashboard.js';
import { initEvent } from 'soslive/pages/event.js';
import { initHome } from 'soslive/pages/home.js';
import { initSettings } from 'soslive/pages/settings.js';

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
        if (status) {
            status.className = 'status error';
            status.textContent = 'Hiba: ' + e.message;
        }
        return;
    }
    renderNav(me);
    if (cfg.page === 'dashboard') initDashboard(me);
    if (cfg.page === 'settings') initSettings(me);
    if (cfg.page === 'home') initHome(me);
}

if (cfg) init();
