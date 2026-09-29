@extends('layouts.app')

@section('title', $user->email.' – SOSlive admin')

@section('content')
    <h1>{{ $user->email }}</h1>
    <p class="muted">
        Google ID: {{ $user->google_id }} · Regisztrált: {{ $user->created_at?->format('Y-m-d H:i') }}
        · Utolsó belépés: {{ $user->last_login_at?->format('Y-m-d H:i') ?? '–' }}
        · Drive mappa: {{ $user->drive_folder_id ?? '–' }}
    </p>

    <form method="post" action="{{ route('admin.users.update', $user) }}" class="stack">
        @csrf
        @method('PUT')
        <label>
            Max események
            <input type="number" name="max_events" min="1" max="100000" required value="{{ old('max_events', $user->max_events) }}">
        </label>
        <button type="submit">Mentés</button>
    </form>

    <h2>Beállítások</h2>
    @include('partials.user-config', ['config' => $config])

    <h2>Törlés</h2>
    <p class="muted">Csak a backend adatai törlődnek; a user Drive-jában lévő fájlok megmaradnak.</p>
    <form method="post" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Biztosan törlöd?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="danger">User törlése</button>
    </form>
@endsection
