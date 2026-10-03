{{-- Márkajel: piros pont (SOS jelzés) és felirat. $href: hová mutat, $label: felirat. --}}
<a class="brand" href="{{ $href }}">
    <svg class="brand-mark" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
        <circle cx="12" cy="12" r="11" fill="currentColor"/>
        <circle cx="12" cy="12" r="4" fill="#fff"/>
    </svg>
    <span>{{ $label }}</span>
</a>
