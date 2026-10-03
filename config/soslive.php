<?php

return [

    // Ha a user config.json-ja nem ad meg max_events-et, ennyi eseményt tart meg (rotáció a mobil appban).
    'default_max_events' => (int) env('SOSLIVE_DEFAULT_MAX_EVENTS', 100),

    // A dashboard legfeljebb ennyi eseményt listáz (a böngésző kéri le a Drive-ból).
    'list_limit' => (int) env('SOSLIVE_LIST_LIMIT', 100),

    // Eseményoldal: ennyi másodpercenként nézzük meg a fájl modifiedTime-ját.
    'poll_seconds' => (int) env('SOSLIVE_POLL_SECONDS', 5),

    'folder_name' => 'SOSlive',

    // Eseményoldal térkép (Leaflet, public/vendor/leaflet): csempe szerver URL sablon és a kötelező forrásmegjelölés.
    // Az openstreetmap.org csempéi csak mérsékelt forgalomra valók (https://operations.osmfoundation.org/policies/tiles/);
    // nagyobb forgalomnál saját vagy szolgáltatói (pl. MapTiler, Stadia, Thunderforest) csempe szerver kell.
    'map' => [
        'tile_url' => env('SOSLIVE_MAP_TILE_URL', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'),
        'attribution' => env('SOSLIVE_MAP_ATTRIBUTION', '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'),
    ],

    // A statikus oldalak (/, /dashboard, /settings, /e/{id}) Cache-Control értékei (másodperc):
    // max_age a böngészőnek, s_maxage a reverse proxynak (Varnish, Cloudflare). Deploy után purge ajánlott.
    'page_cache' => [
        'max_age' => (int) env('SOSLIVE_PAGE_MAX_AGE', 60),
        's_maxage' => (int) env('SOSLIVE_PAGE_S_MAXAGE', 3600),
    ],

    'scopes' => [
        'openid',
        'email',
        'profile',
        'https://www.googleapis.com/auth/drive.file',
    ],

];
