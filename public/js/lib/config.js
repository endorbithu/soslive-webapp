// A Blade által kiírt publikus konfiguráció (resources/views/partials/config.blade.php) és a közös állandók.
const node = document.getElementById('soslive-config');

export const cfg = node ? JSON.parse(node.textContent) : null;

export const DRIVE = 'https://www.googleapis.com/drive/v3/files';
export const HLS_JS = 'https://cdn.jsdelivr.net/npm/hls.js@1/dist/hls.min.js';
export const GIS_JS = 'https://accounts.google.com/gsi/client';
export const FOLDER_MIME = 'application/vnd.google-apps.folder';
