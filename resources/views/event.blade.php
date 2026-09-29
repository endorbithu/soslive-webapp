@extends('layouts.app')

@section('title', 'Esemény – SOSlive')

@section('content')
    <h1 id="event-title">Esemény</h1>
    <p id="event-status" class="muted">Betöltés…</p>

    <div id="event" hidden>
        <div id="stream" class="stream"></div>
        <p id="position"></p>

        <h2>Idővonal</h2>
        <ol id="timeline" class="timeline"></ol>

        <form id="chat" class="chat" hidden>
            <input type="text" name="message" maxlength="1000" placeholder="Üzenet…" required autocomplete="off">
            <button type="submit">Küldés</button>
        </form>
        @guest
            <p class="muted"><a href="{{ route('auth.google') }}">Lépj be</a>, ha üzenetet írnál (csak jogosult felhasználók).</p>
        @endguest
    </div>

    @include('partials.config', ['config' => ['page' => 'event', 'spreadsheetId' => $spreadsheetId, 'owners' => $owners]])
@endsection
