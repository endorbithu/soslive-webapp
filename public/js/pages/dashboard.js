// Dashboard: a saját eseménylista, a böngésző olvassa a Drive-ból.
import { cfg } from 'soslive/lib/config.js';
import { displayName, el, formatTime } from 'soslive/lib/dom.js';
import { createFolder, findFolder, listEvents, readConfig, withDrive } from 'soslive/lib/drive.js';
import { loginPrompt } from 'soslive/lib/ui.js';

function eventItem(f) {
    return el('li', {}, [
        el('a', { class: 'event-link', href: '/e/' + encodeURIComponent(f.id) }, [
            el('span', { class: 'event-name', text: displayName(f.name) || f.id }),
            el('span', { class: 'event-meta', text: formatTime(f.createdTime) }),
        ]),
    ]);
}

export function initDashboard(me) {
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
            status.className = 'status empty';
            status.replaceChildren(
                el('span', { text: 'Még nincs SOSlive mappa a Drive-odban (a mobil app az első eseménynél létrehozza).' }),
                button,
            );
            return;
        }

        const config = await readConfig(me, folderId);
        const maxEvents = config ? config.max_events : cfg.defaultMaxEvents;
        const events = await listEvents(me, folderId, Math.min(cfg.listLimit, maxEvents));

        status.className = events.length ? 'status' : 'status empty';
        status.textContent = events.length ? '' : 'Még nincs esemény. Az eseményeket a SOSlive mobil app rögzíti.';
        status.hidden = !!events.length;
        list.replaceChildren(...events.map(eventItem));
    };
    withDrive(me, load);
}
