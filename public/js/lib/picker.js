// Google Picker: a user kiválasztja a vele megosztott SOSlive `events` mappát. A kiválasztással a drive.file token
// hozzáférést kap a mappához (ehhez kell az App ID = a Google Cloud projekt száma).
import { cfg, FOLDER_MIME, GAPI_JS } from 'soslive/lib/config.js';
import { loadScript } from 'soslive/lib/dom.js';
import { driveAccessToken } from 'soslive/lib/drive.js';

let pickerReady = null;

function loadPicker() {
    pickerReady = pickerReady || loadScript(GAPI_JS).then(() => new Promise((resolve, reject) => {
        window.gapi.load('picker', { callback: resolve, onerror: () => reject(new Error('A Google Picker nem tölthető be.')) });
    })).catch((e) => {
        pickerReady = null;
        throw e;
    });
    return pickerReady;
}

export function pickerAvailable() {
    return Boolean(cfg.googleAppId && cfg.apiKey && cfg.googleClientId);
}

/** Megnyitja a Pickert; a kiválasztott mappák azonosítóival tér vissza (megszakításnál üres lista). */
export async function pickSharedFolders(me) {
    const [token] = await Promise.all([driveAccessToken(me), loadPicker()]);
    const picker = window.google.picker;

    return new Promise((resolve) => {
        const view = new picker.DocsView(picker.ViewId.FOLDERS)
            .setIncludeFolders(true)
            .setSelectFolderEnabled(true)
            .setOwnedByMe(false)
            .setMimeTypes(FOLDER_MIME);

        new picker.PickerBuilder()
            .addView(view)
            .enableFeature(picker.Feature.MULTISELECT_ENABLED)
            .setOAuthToken(token)
            .setDeveloperKey(cfg.apiKey)
            .setAppId(cfg.googleAppId)
            .setLocale('hu')
            .setTitle('Válaszd ki a veled megosztott SOSlive „events” mappát')
            .setCallback((data) => {
                if (data.action === picker.Action.PICKED) resolve((data.docs || []).map((d) => d.id));
                else if (data.action === picker.Action.CANCEL) resolve([]);
            })
            .build()
            .setVisible(true);
    });
}
