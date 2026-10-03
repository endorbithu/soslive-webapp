{{--
    A böngésző JS-e: publikus, mindenkinek azonos adat (a HTML reverse proxyban cache-elődik).
    A modulok (public/js/**) build lépés nélkül, natív ES modulként töltődnek. Az import map minden modulhoz
    ?v=filemtime verziót ad, így deploy után a böngésző és a proxy cache sem ad vissza régi modult.
    A modulok egymást „soslive/…” névvel importálják (pl. import { el } from 'soslive/lib/dom.js').
--}}
@php
    $jsConfig = [
        'page' => trim($__env->yieldContent('page')),
        'apiKey' => config('services.google.api_key'),
        'googleClientId' => config('services.google.client_id'),
        'driveScope' => 'https://www.googleapis.com/auth/drive.file',
        'folderName' => config('soslive.folder_name'),
        'defaultMaxEvents' => config('soslive.default_max_events'),
        'listLimit' => config('soslive.list_limit'),
        'pollSeconds' => config('soslive.poll_seconds'),
        'meUrl' => route('me', [], false),
        'loginUrl' => route('auth.google', [], false),
        'logoutUrl' => route('logout', [], false),
        'mapTileUrl' => config('soslive.map.tile_url'),
        'mapAttribution' => config('soslive.map.attribution'),
    ];
    if (! app()->isProduction()) {
        $jsConfig['demoUrl'] = '/app/dev/drive/files'; // demó események Google nélkül (DemoDriveController)
    }

    $jsRoot = public_path('js');
    $jsModules = collect(\Illuminate\Support\Facades\File::allFiles($jsRoot))
        ->filter(fn ($file) => $file->getExtension() === 'js')
        ->mapWithKeys(function ($file) {
            $path = str_replace(DIRECTORY_SEPARATOR, '/', $file->getRelativePathname());

            return ['soslive/'.$path => '/js/'.$path.'?v='.$file->getMTime()];
        })
        ->sortKeys();
@endphp
<script type="application/json" id="soslive-config">@json($jsConfig)</script>
<script type="importmap">@json(['imports' => $jsModules], JSON_UNESCAPED_SLASHES)</script>
<script type="module" src="{{ $jsModules['soslive/app.js'] }}"></script>
