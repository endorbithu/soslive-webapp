@extends('layouts.app')

@section('title', 'Beállítások – SOSlive')
@section('page', 'settings')

@section('content')
    <div class="page-head">
        <h1>Beállítások</h1>
        {{-- A beállítások a Drive-odon lévő config.json-ból jönnek; a böngésző olvassa be, a backend nem látja. --}}
        <p class="muted">Ezek a beállítások csak a SOSlive mobil appban módosíthatók.</p>
    </div>
    <p id="page-status" class="status">Betöltés…</p>
    <p id="drive-access" class="status" hidden>
        <button type="button">Google Drive hozzáférés engedélyezése</button>
    </p>

    <div id="settings" hidden>
        <p id="cfg-missing" class="status empty" hidden>Még nincs beállítás – a mobil appban adhatod meg.</p>
        <section class="card">
            <dl class="config">
                <dt>Értesítendő email címek</dt>
                <dd id="cfg-notification-emails"></dd>
                <dt>Értesítendő telefonszámok</dt>
                <dd id="cfg-notification-phones"></dd>
                <dt>Megtartott események száma</dt>
                <dd id="cfg-max-events"></dd>
            </dl>
        </section>

        <section class="card">
            <h2>Google Drive</h2>
            <p>Eseményeid a <strong>{{ config('soslive.folder_name') }}</strong> mappában vannak.</p>
            <p><a id="cfg-folder-link" class="button secondary" href="#" target="_blank" rel="noopener" hidden>Megnyitás a Drive-ban</a></p>
        </section>
    </div>
@endsection
