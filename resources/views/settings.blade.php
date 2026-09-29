@extends('layouts.app')

@section('title', 'Beállítások – SOSlive')

@section('content')
    <h1>Beállítások</h1>

    <form method="post" action="{{ route('settings.update') }}" class="stack">
        @csrf
        @method('PUT')
        @include('partials.user-fields', ['user' => $user, 'allowedEmails' => $allowedEmails])
        <button type="submit">Mentés</button>
    </form>

    <h2>Google Drive</h2>
    <p>
        Eseményeid a <strong>{{ config('soslive.folder_name') }}</strong> mappában vannak.
        @if ($user->drive_folder_id)
            <a href="https://drive.google.com/drive/folders/{{ $user->drive_folder_id }}" target="_blank" rel="noopener">Megnyitás a Drive-ban</a>
        @endif
    </p>
    <p class="muted">Legfeljebb {{ $user->max_events }} eseményt tartunk meg, a régebbiek a Drive kukába kerülnek.
        Ha törölted a mappát, itt létrehozhatsz egy újat (a régi események nem jönnek vissza).</p>
    <form method="post" action="{{ route('settings.folder') }}">
        @csrf
        <button type="submit">Mappa ellenőrzése / újralétrehozása</button>
    </form>
@endsection
