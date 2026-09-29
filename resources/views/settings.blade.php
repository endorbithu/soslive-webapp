@extends('layouts.app')

@section('title', 'Beállítások – SOSlive')
@section('page', 'settings')

@section('content')
    <h1>Beállítások</h1>
    <p id="page-status" class="muted">Betöltés…</p>

    {{-- A tartalmat a JS tölti ki az /app/me válaszából. --}}
    <div id="settings" hidden>
        <p class="note">Ezek a beállítások csak a SOSlive mobil appban módosíthatók.</p>
        <dl class="config">
            <dt>Értesítendő email címek <span class="muted">(ők automatikusan hozzáférést is kapnak)</span></dt>
            <dd id="cfg-notification-emails"></dd>
            <dt>Értesítendő telefonszámok</dt>
            <dd id="cfg-notification-phones"></dd>
            <dt>Hozzáférés az eseményekhez <span class="muted">(belépés után látják az eseménylistát)</span></dt>
            <dd id="cfg-allowed-emails"></dd>
        </dl>

        <h2>Google Drive</h2>
        <p>
            Eseményeid a <strong>{{ config('soslive.folder_name') }}</strong> mappában vannak.
            <a id="cfg-folder-link" href="#" target="_blank" rel="noopener" hidden>Megnyitás a Drive-ban</a>
        </p>
        <p class="muted">Legfeljebb <span id="cfg-max-events"></span> eseményt tartunk meg, a régebbiek a Drive kukába kerülnek.
            Ha törölted a mappát, itt létrehozhatsz egy újat (a régi események nem jönnek vissza).</p>
        <form method="post" action="{{ route('settings.folder', [], false) }}" class="csrf-form">
            <button type="submit">Mappa ellenőrzése / újralétrehozása</button>
        </form>
    </div>
@endsection
