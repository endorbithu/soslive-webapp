@extends('layouts.app')

@section('title', 'Beállítások – SOSlive')
@section('page', 'settings')

@section('content')
    <h1>Beállítások</h1>
    {{-- A beállítások a Drive-odon lévő config.json-ból jönnek; a böngésző olvassa be, a backend nem látja. --}}
    <p id="page-status" class="muted">Betöltés…</p>
    <p id="drive-access" hidden>
        <button type="button">Google Drive hozzáférés engedélyezése</button>
    </p>

    <div id="settings" hidden>
        <p class="note">Ezek a beállítások csak a SOSlive mobil appban módosíthatók.</p>
        <p id="cfg-missing" class="muted" hidden>Még nincs beállítás – a mobil appban adhatod meg.</p>
        <dl class="config">
            <dt>Értesítendő email címek</dt>
            <dd id="cfg-notification-emails"></dd>
            <dt>Értesítendő telefonszámok</dt>
            <dd id="cfg-notification-phones"></dd>
            <dt>Megtartott események száma</dt>
            <dd id="cfg-max-events"></dd>
        </dl>

        <h2>Google Drive</h2>
        <p>
            Eseményeid a <strong>{{ config('soslive.folder_name') }}</strong> mappában vannak.
            <a id="cfg-folder-link" href="#" target="_blank" rel="noopener" hidden>Megnyitás a Drive-ban</a>
        </p>
    </div>
@endsection
