{{-- A böngésző JS konfigurációja. Csak publikus / a user számára amúgy is látható adat kerülhet ide. --}}
@php
    $jsConfig = array_merge([
        'apiKey' => config('services.google.api_key'),
        'pollSeconds' => config('soslive.poll_seconds'),
        'eventsUrl' => url('/events'),
    ], $config ?? []);
@endphp
<script type="application/json" id="soslive-config">@json($jsConfig)</script>
<script src="{{ asset('js/soslive.js') }}" defer></script>
