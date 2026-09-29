<?php

return [

    // Ennyi eseményt (esemény fájlt) tarthat meg alapból egy user; a többit a rotáció kukába teszi.
    'default_max_events' => (int) env('SOSLIVE_DEFAULT_MAX_EVENTS', 100),

    // Egy tulaj eseménylistájában legfeljebb ennyit kérünk le a Drive-ból.
    'list_limit' => (int) env('SOSLIVE_LIST_LIMIT', 100),

    // Eseményoldal: ennyi másodpercenként nézzük meg a fájl modifiedTime-ját.
    'poll_seconds' => (int) env('SOSLIVE_POLL_SECONDS', 5),

    'folder_name' => 'SOSlive',

    'scopes' => [
        'openid',
        'email',
        'profile',
        'https://www.googleapis.com/auth/drive.file',
    ],

];
