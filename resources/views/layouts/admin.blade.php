{{-- Admin layout: dinamikus (session, CSRF), az /app/admin alatt; nem cache-elődik. --}}
<!doctype html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'SOSlive admin')</title>
    <link rel="stylesheet" href="/css/app.css?v={{ filemtime(public_path('css/app.css')) }}">
</head>
<body>
<header class="top">
    <a class="brand" href="{{ route('admin.users.index') }}">SOSlive admin</a>
    <nav>
        @auth('admin')
            <a href="{{ route('admin.users.index') }}">Userek</a>
            <form method="post" action="{{ route('admin.logout') }}" class="inline">
                @csrf
                <button type="submit" class="link">Kilépés</button>
            </form>
        @endauth
    </nav>
</header>
<main>
    @if (session('status'))
        <p class="flash ok">{{ session('status') }}</p>
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
</body>
</html>
