{{-- A böngésző JS konfigurációja: csak publikus, mindenkinek azonos adat (a HTML reverse proxyban cache-elődik). --}}
@php
    $jsConfig = [
        'page' => trim($__env->yieldContent('page')),
        'apiKey' => config('services.google.api_key'),
        'pollSeconds' => config('soslive.poll_seconds'),
        'meUrl' => route('me', [], false),
        'eventsUrl' => '/app/events',
        'loginUrl' => route('auth.google', [], false),
        'logoutUrl' => route('logout', [], false),
        'folderUrl' => route('settings.folder', [], false),
    ];
@endphp
<script type="application/json" id="soslive-config">@json($jsConfig)</script>
<script src="/js/soslive.js?v={{ filemtime(public_path('js/soslive.js')) }}" defer></script>
