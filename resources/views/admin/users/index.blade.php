@extends('layouts.app')

@section('title', 'Userek – SOSlive admin')

@section('content')
    <h1>Userek</h1>
    <form method="get" class="row">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Email vagy név">
        <button type="submit">Keresés</button>
    </form>

    <div class="table-wrap">
    <table>
        <thead>
        <tr><th>Email</th><th>Név</th><th>Utolsó belépés</th><th>Max esemény</th><th>Hozzáférők</th><th>Drive</th><th></th></tr>
        </thead>
        <tbody>
        @forelse ($users as $user)
            <tr>
                <td>{{ $user->email }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->last_login_at?->format('Y-m-d H:i') }}</td>
                <td>{{ $user->max_events }}</td>
                <td>{{ $user->allowed_emails_count }}</td>
                <td>{{ $user->google_refresh_token ? 'OK' : 'újra belépés kell' }}</td>
                <td><a href="{{ route('admin.users.edit', $user) }}">Szerkesztés</a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="muted">Nincs user.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>

    <p class="row">
        @if ($users->previousPageUrl())
            <a href="{{ $users->previousPageUrl() }}">&larr; Előző</a>
        @endif
        <span class="muted">{{ $users->currentPage() }} / {{ $users->lastPage() }} oldal · {{ $users->total() }} user</span>
        @if ($users->nextPageUrl())
            <a href="{{ $users->nextPageUrl() }}">Következő &rarr;</a>
        @endif
    </p>
@endsection
