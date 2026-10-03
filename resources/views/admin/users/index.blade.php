@extends('layouts.admin')

@section('title', 'Userek – SOSlive admin')

@section('content')
    <h1>Userek</h1>
    <form method="get" class="row search">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Email vagy név">
        <button type="submit">Keresés</button>
    </form>

    <div class="card flush table-wrap">
    <table>
        <thead>
        <tr><th>Email</th><th>Név</th><th>Regisztrált</th><th>Utolsó belépés</th><th></th></tr>
        </thead>
        <tbody>
        @forelse ($users as $user)
            <tr>
                <td>{{ $user->email }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->created_at?->format('Y-m-d H:i') }}</td>
                <td>{{ $user->last_login_at?->format('Y-m-d H:i') }}</td>
                <td>
                    <form method="post" action="{{ route('admin.users.destroy', $user) }}" class="inline"
                          onsubmit="return confirm('Biztosan törlöd? (A user Drive-jában lévő fájlok megmaradnak.)')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="link">Törlés</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">Nincs user.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>

    <p class="row pager">
        @if ($users->previousPageUrl())
            <a href="{{ $users->previousPageUrl() }}">&larr; Előző</a>
        @endif
        <span class="muted">{{ $users->currentPage() }} / {{ $users->lastPage() }} oldal · {{ $users->total() }} user</span>
        @if ($users->nextPageUrl())
            <a href="{{ $users->nextPageUrl() }}">Következő &rarr;</a>
        @endif
    </p>
@endsection
