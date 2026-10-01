{{--
    Statikus layout: reverse proxy (Varnish, Cloudflare) cache-eli, ezért mindenkinek ugyanaz a HTML kell legyen.
    TILOS ide userfüggő adatot tenni (auth(), session(), csrf_token(), $errors) – ezeket a JS tölti be az /app/me-ből.
    Az URL-ek relatívak, hogy a cache-elt HTML ne függjön a kérés hostjától / sémájától.
--}}
<!doctype html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    @stack('head')
    <title>@yield('title', 'SOSlive')</title>
    <link rel="stylesheet" href="/css/app.css?v={{ filemtime(public_path('css/app.css')) }}">
</head>
<body>
<header class="top">
    <a class="brand" href="{{ route('home', [], false) }}">SOSlive</a>
    <nav id="nav">
        <a href="{{ route('auth.google', [], false) }}">Belépés</a>
        @unless (app()->isProduction())
            <a href="{{ route('auth.dev', [], false) }}">Teszt belépés</a>
        @endunless
    </nav>
</header>
<main>
    <p id="flash" class="flash" hidden></p>

    @yield('content')
</main>
@include('partials.config')
</body>
</html>
