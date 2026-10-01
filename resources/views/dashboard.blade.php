@extends('layouts.app')

@section('title', 'Események – SOSlive')
@section('page', 'dashboard')

@section('content')
    <h1>Eseményeim</h1>
    {{-- A listát a böngésző tölti be a saját Google Drive-odból (a SOSlive backend nem látja). --}}
    <p id="page-status" class="muted">Betöltés…</p>
    <p id="drive-access" hidden>
        <button type="button">Google Drive hozzáférés engedélyezése</button>
    </p>
    <ul id="events" class="events"></ul>

    @include('partials.demo-events')
@endsection
