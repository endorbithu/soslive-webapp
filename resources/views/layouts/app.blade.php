{{--
    Statikus layout: reverse proxy (Varnish, Cloudflare) cache-eli, ezért mindenkinek ugyanaz a HTML kell legyen.
    TILOS ide userfüggő adatot tenni (auth(), session(), csrf_token(), $errors) – ezeket a JS tölti be az /app/me-ből.
    Az URL-ek relatívak, hogy a cache-elt HTML ne függjön a kérés hostjától / sémájától.
--}}
<!doctype html>
<html lang="hu">
<head>
    @include('partials.head')
    @stack('head')
    <title>@yield('title', 'SOSlive')</title>
</head>
<body>
<header class="top">
    <div class="container top-inner">
        @include('partials.brand', ['href' => route('home', [], false), 'label' => 'SOSlive'])
        <nav id="nav">
            <a href="{{ route('auth.google', [], false) }}">Belépés</a>
            @unless (app()->isProduction())
                <a href="{{ route('auth.dev', [], false) }}">Teszt belépés</a>
            @endunless
        </nav>
    </div>
</header>
<main class="container">
    <p id="flash" class="flash" hidden></p>

    @yield('content')
</main>
<footer class="site-footer">
    <div class="container">
        Az eseményeid a saját Google Drive-odban vannak, a SOSlive nem tárolja őket.
        @unless (app()->isProduction())
            <span class="env">{{ app()->environment() }} környezet</span>
        @endunless
    </div>
</footer>
@include('partials.config')
</body>
</html>
