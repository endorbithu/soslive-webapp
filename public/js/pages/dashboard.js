// Dashboard: a saját eseménylista és a velem megosztott események, a böngésző olvassa a Drive-ból.
import { cfg } from 'soslive/lib/config.js';
import { displayName, el, formatTime } from 'soslive/lib/dom.js';
import {
    createFolder, findEventsFolder, findFolder, folderTag, listEvents, listSharedEventsFolders, readConfig, TokenError, withDrive,
} from 'soslive/lib/drive.js';
import { pickerAvailable, pickSharedFolders } from 'soslive/lib/picker.js';
import { loginPrompt } from 'soslive/lib/ui.js';

function eventItem(f) {
    return el('li', {}, [
        el('a', { class: 'event-link', href: '/e/' + encodeURIComponent(f.id) }, [
            el('span', { class: 'event-name', text: displayName(f.name) || f.id }),
            el('span', { class: 'event-meta', text: formatTime(f.createdTime) }),
        ]),
    ]);
}

function setStatus(node, kind, children) {
    node.className = 'status' + (kind ? ' ' + kind : '');
    node.replaceChildren(...children);
    node.hidden = !children.length;
}

async function loadOwn(me, status, list, reload) {
    const rootId = await findFolder(me);
    if (!rootId) {
        const button = el('button', { type: 'button', text: 'SOSlive mappa létrehozása' });
        button.addEventListener('click', () => withDrive(me, async () => {
            await createFolder(me);
            await reload();
        }));
        setStatus(status, 'empty', [
            el('span', { text: 'Még nincs SOSlive mappa a Drive-odban (a mobil app az első eseménynél létrehozza).' }),
            button,
        ]);
        list.replaceChildren();
        return;
    }

    const [config, eventsId] = await Promise.all([readConfig(me, rootId), findEventsFolder(me, rootId)]);
    const maxEvents = config ? config.max_events : cfg.defaultMaxEvents;
    // Az `events` mappa mellett a SOSlive mappát is nézzük: a még nem frissített mobil app oda írta az eseményeket.
    const events = await listEvents(me, [eventsId, rootId], Math.min(cfg.listLimit, maxEvents));

    setStatus(status, events.length ? '' : 'empty', events.length ? [] : [
        el('span', { text: 'Még nincs esemény. Az eseményeket a SOSlive mobil app rögzíti.' }),
    ]);
    list.replaceChildren(...events.map(eventItem));
}

/** Egy megosztott mappa eseményei; ha közben visszavonták a hozzáférést (403 / 404), null – a mappa csendben kimarad. */
async function sharedBlock(me, folder) {
    let events;
    try {
        events = await listEvents(me, [folder.id], cfg.listLimit);
    } catch (e) {
        if (e.status === 403 || e.status === 404) return null;
        throw e;
    }
    const who = folder.ownerName || folder.ownerEmail || 'Ismeretlen';
    return el('div', { class: 'shared-owner' }, [
        el('h3', {}, [
            el('span', { text: who }),
            folder.ownerName && folder.ownerEmail ? el('span', { class: 'muted', text: ' ' + folder.ownerEmail }) : null,
        ]),
        events.length
            ? el('ul', { class: 'events' }, events.map(eventItem))
            : el('p', { class: 'status empty', text: 'Még nincs esemény (vagy a Google Drive nem adott hozzáférést a mappa tartalmához).' }),
    ]);
}

async function loadShared(me, section, status, list) {
    section.hidden = false;
    const folders = await listSharedEventsFolders(me);
    const blocks = (await Promise.all(folders.map((f) => sharedBlock(me, f)))).filter(Boolean);
    list.replaceChildren(...blocks);
    if (!blocks.length) {
        setStatus(status, 'empty', [el('span', {
            text: pickerAvailable()
                ? 'Még nincs veled megosztott esemény mappa.'
                : 'Még nincs veled megosztott esemény mappa. (A mappa hozzáadásához a szerveren be kell állítani a GOOGLE_APP_ID-t.)',
        })]);
    } else {
        setStatus(status, '', []);
    }
}

export function initDashboard(me) {
    const status = document.getElementById('page-status');
    if (!me.user) return loginPrompt(status, 'Az események megtekintéséhez lépj be.');

    const list = document.getElementById('events');
    const section = document.getElementById('shared');
    const sharedStatus = document.getElementById('shared-status');
    const sharedList = document.getElementById('shared-list');
    const addButton = document.getElementById('shared-add');

    const load = async () => {
        await loadOwn(me, status, list, load);
        try {
            await loadShared(me, section, sharedStatus, sharedList);
        } catch (e) {
            if (e instanceof TokenError) throw e;
            setStatus(sharedStatus, 'error', [el('span', { text: 'A megosztott események nem tölthetők be: ' + e.message })]);
        }
    };

    addButton.hidden = !pickerAvailable();
    addButton.addEventListener('click', async () => {
        if (sharedStatus.classList.contains('error')) setStatus(sharedStatus, '', []);
        try {
            const picked = await pickSharedFolders(me);
            if (!picked.length) return;
            const tags = await Promise.all(picked.map((id) => folderTag(me, id).catch(() => null)));
            if (tags.some((t) => t !== 'events')) {
                setStatus(sharedStatus, 'error', [el('span', {
                    text: 'Ez nem egy SOSlive események mappa – a megosztásról szóló Google emailben szereplő „events” mappát válaszd.',
                })]);
                if (!tags.includes('events')) return;
            }
            await loadShared(me, section, sharedStatus, sharedList);
        } catch (e) {
            setStatus(sharedStatus, 'error', [el('span', { text: 'Hiba: ' + e.message })]);
        }
    });

    withDrive(me, load);
}
