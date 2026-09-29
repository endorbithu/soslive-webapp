{{-- A böngésző JS konfigurációja. Csak publikus / a user számára amúgy is látható adat kerülhet ide. --}}
@php
    $me = auth()->user();
    $jsConfig = array_merge([
        'apiKey' => config('services.google.api_key'),
        'listLimit' => config('soslive.list_limit'),
        'pollSeconds' => config('soslive.poll_seconds'),
        'tokenUrl' => url('/token'),
        'me' => $me ? ['name' => $me->name ?: $me->email, 'email' => $me->email] : null,
    ], $config ?? []);
@endphp
<script type="application/json" id="soslive-config">@json($jsConfig)</script>
<script src="{{ asset('js/soslive.js') }}" defer></script>
