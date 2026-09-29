@extends('layouts.app')

@section('title', 'Esemény – SOSlive')

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
    <h1 id="event-title">Esemény</h1>
    <p id="event-status" class="muted">Betöltés…</p>

    <div id="event" hidden>
        <div id="stream" class="stream"></div>
        <p id="position"></p>

        <h2>Idővonal</h2>
        <ol id="timeline" class="timeline"></ol>

        <p>
            <a href="https://docs.google.com/spreadsheets/d/{{ $spreadsheetId }}/edit" target="_blank" rel="noopener">Megnyitás Google Sheetsben</a>
            <span class="muted">– ha az esemény tulajdonosa megosztotta veled, ott üzenetet is írhatsz (új sor, E oszlop).</span>
        </p>
    </div>

    @include('partials.config', ['config' => ['page' => 'event', 'spreadsheetId' => $spreadsheetId]])
@endsection
