@extends('layouts.app')

@section('title', 'Események – SOSlive')

@section('content')
    <h1>Események</h1>
    <div id="owners">
        @foreach ($owners as $owner)
            <section class="owner" data-owner-id="{{ $owner['id'] }}">
                <h2>{{ $owner['is_me'] ? 'Saját eseményeim' : $owner['name'].' ('.$owner['email'].')' }}</h2>
                <p class="status muted">Betöltés…</p>
                <ul class="events"></ul>
            </section>
        @endforeach
    </div>

    <template id="folder-missing">
        <form method="post" action="{{ route('settings.folder') }}">
            @csrf
            <button type="submit">SOSlive mappa létrehozása</button>
        </form>
    </template>

    @include('partials.config', ['config' => ['page' => 'dashboard', 'owners' => $owners]])
@endsection
