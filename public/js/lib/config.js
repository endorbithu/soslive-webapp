// A Blade által kiírt publikus konfiguráció (resources/views/partials/config.blade.php) és a közös állandók.
const node = document.getElementById('soslive-config');

export const cfg = node ? JSON.parse(node.textContent) : null;

export const DRIVE = 'https://www.googleapis.com/drive/v3/files';
export const HLS_JS = 'https://cdn.jsdelivr.net/npm/hls.js@1/dist/hls.min.js';
export const GIS_JS = 'https://accounts.google.com/gsi/client';
export const GAPI_JS = 'https://apis.google.com/js/api.js'; // Google Picker
// Saját példány (public/vendor), verziózott útvonalon: nincs külső CDN, és a cache sem ad vissza régi verziót.
export const LEAFLET = '/vendor/leaflet/1.9.4/leaflet';
export const FOLDER_MIME = 'application/vnd.google-apps.folder';
