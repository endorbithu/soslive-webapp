// Beállítások: a mobil app által írt config.json, csak olvasható.
import { cfg } from 'soslive/lib/config.js';
import { el } from 'soslive/lib/dom.js';
import { findEventsFolder, findFolder, listViewers, readConfig, withDrive } from 'soslive/lib/drive.js';
import { loginPrompt } from 'soslive/lib/ui.js';

export function initSettings(me) {
    const status = document.getElementById('page-status');
    if (!me.user) return loginPrompt(status, 'A beállítások megtekintéséhez lépj be.');

    const fill = (id, items) => {
        const dd = document.getElementById(id);
        dd.replaceChildren();
        if (!items.length) dd.append(el('span', { class: 'muted', text: 'nincs megadva' }));
        else dd.append(el('ul', { class: 'plain' }, items.map((item) => el('li', { text: item }))));
    };

    const fillViewers = (eventsId, viewers) => {
        const box = document.getElementById('cfg-viewers');
        if (!eventsId) {
            box.replaceChildren(el('p', { class: 'muted', text: 'A mobil app frissítése után jelenik meg.' }));
        } else if (!viewers.length) {
            box.replaceChildren(el('p', { class: 'muted', text: 'Senki – a mobil appban adhatod meg.' }));
        } else {
            box.replaceChildren(el('ul', { class: 'plain viewers' }, viewers.map((v) => el('li', {}, [
                el('span', { text: v.name || v.email }),
                v.name && v.email ? el('span', { class: 'muted', text: ' ' + v.email }) : null,
            ]))));
        }
    };

    withDrive(me, async () => {
        const folderId = await findFolder(me);
        const [config, eventsId] = folderId
            ? await Promise.all([readConfig(me, folderId), findEventsFolder(me, folderId)])
            : [null, null];
        fillViewers(eventsId, eventsId ? await listViewers(me, eventsId) : []);

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
