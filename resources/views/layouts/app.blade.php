<!doctype html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    @stack('head')
    <title>@yield('title', 'SOSlive')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<header class="top">
    <a class="brand" href="{{ route('home') }}">SOSlive</a>
    <nav>
        @if (request()->is('admin', 'admin/*'))
            @auth('admin')
                <a href="{{ route('admin.users.index') }}">Userek</a>
                <form method="post" action="{{ route('admin.logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="link">Kilépés</button>
                </form>
            @endauth
        @else
            @auth
                <a href="{{ route('dashboard') }}">Események</a>
                <a href="{{ route('settings') }}">Beállítások</a>
                <span class="muted">{{ auth()->user()->email }}</span>
                <form method="post" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="link">Kilépés</button>
                </form>
            @else
                <a href="{{ route('auth.google') }}">Belépés</a>
            @endauth
        @endif
    </nav>
</header>
<main>
    @if (session('status'))
        <p class="flash ok">{{ session('status') }}</p>
    @endif
    @if (session('error'))
        <p class="flash err">{{ session('error') }}</p>
    @endif
    @if ($errors->any())
        <ul class="flash err">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    @yield('content')
</main>
@stack('scripts')
</body>
</html>
