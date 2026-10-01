{{-- A böngésző JS konfigurációja: csak publikus, mindenkinek azonos adat (a HTML reverse proxyban cache-elődik). --}}
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
    ];
    if (! app()->isProduction()) {
        $jsConfig['demoUrl'] = '/app/dev/drive/files'; // demó események Google nélkül (DemoDriveController)
    }
@endphp
<script type="application/json" id="soslive-config">@json($jsConfig)</script>
<script src="/js/soslive.js?v={{ filemtime(public_path('js/soslive.js')) }}" defer></script>
