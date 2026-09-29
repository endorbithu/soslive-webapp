{{-- A user config csak olvasható megjelenítése; módosítani csak a mobil appban lehet. --}}
<p class="note">Ezek a beállítások csak a SOSlive mobil appban módosíthatók.</p>
<dl class="config">
    <dt>Értesítendő email címek <span class="muted">(ők automatikusan hozzáférést is kapnak)</span></dt>
    <dd>{{ implode(', ', $config['notification_emails']) ?: '–' }}</dd>

    <dt>Értesítendő telefonszámok</dt>
    <dd>{{ implode(', ', $config['notification_phones']) ?: '–' }}</dd>

    <dt>Hozzáférés az eseményekhez <span class="muted">(belépés után látják az eseménylistát)</span></dt>
    <dd>
        @forelse ($config['allowed_emails'] as $email)
            {{ $email }}@if (! $loop->last)<br>@endif
        @empty
            –
        @endforelse
    </dd>
</dl>
