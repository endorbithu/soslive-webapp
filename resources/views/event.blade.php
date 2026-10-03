@extends('layouts.app')

@section('title', 'Esemény – SOSlive')
@section('page', 'event')

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
    <div class="event-head">
        <h1 id="event-title">Esemény</h1>
        <span id="event-badge" class="badge" hidden></span>
    </div>
    <p id="event-status" class="status">Betöltés…</p>

    <div id="event" class="event" data-file-id="{{ $fileId }}" hidden>
        <section class="card flush">
            <div id="stream" class="stream"></div>
        </section>

        <div class="event-side">
            <section class="card">
                <h2>Utolsó pozíció</h2>
                <p id="position" class="position"></p>
            </section>

            <p class="note">SMS-ben válaszolhatsz arra a számra, ahonnan az értesítést kaptad.</p>
        </div>

        <section class="card event-timeline">
            <h2>Idővonal</h2>
            <ol id="timeline" class="timeline"></ol>
        </section>
    </div>
@endsection
