@extends('layouts.app')

@section('title', 'Események – SOSlive')
@section('page', 'dashboard')

@section('content')
    <div class="page-head">
        <h1>Eseményeim</h1>
        <p class="muted">A lista a saját Google Drive-odból töltődik be, a SOSlive szerver nem látja.</p>
    </div>
    <p id="page-status" class="status">Betöltés…</p>
    <p id="drive-access" class="status" hidden>
        <button type="button">Google Drive hozzáférés engedélyezése</button>
    </p>
    <ul id="events" class="events"></ul>

    {{-- Mások események mappái, amelyeket megosztottak veled (a mobil appban), és egyszer kiválasztottál a Google Pickerben. --}}
    <section id="shared" class="shared" hidden>
        <div class="section-head">
            <h2>Velem megosztott események</h2>
            <button id="shared-add" type="button" class="secondary" hidden>Megosztott mappa hozzáadása</button>
        </div>
        <p class="muted">Ha valaki megosztotta veled a SOSlive eseményeit, a Google értesítő emailjében szereplő
            <strong>events</strong> mappát egyszer itt kell kiválasztanod. Utána az új eseményei is itt jelennek meg.</p>
        <p id="shared-status" class="status" hidden></p>
        <div id="shared-list"></div>
    </section>

    @include('partials.demo-events')
@endsection
