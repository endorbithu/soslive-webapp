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

    @include('partials.demo-events')
@endsection
