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
        <p>
            Az eseményeid a saját Google Drive-odban vannak, a SOSlive nem tárolja őket.
            @unless (app()->isProduction())
                <span class="env">{{ app()->environment() }} környezet</span>
            @endunless
        </p>
        {{-- Statikus PDF-ek a public/legal alól (forrás: resources/legal). --}}
        <nav class="legal" aria-label="Jogi információk">
            <a href="/legal/adatvedelem.pdf">Adatvédelmi tájékoztató</a>
            <a href="/legal/felhasznalasi-feltetelek.pdf">Felhasználási feltételek</a>
            <a href="/legal/privacy-policy.pdf" hreflang="en" lang="en">Privacy Policy</a>
            <a href="/legal/terms-of-service.pdf" hreflang="en" lang="en">Terms of Service</a>
            <a href="mailto:info@endorbit.hu">info@endorbit.hu</a>
        </nav>
    </div>
</footer>
@include('partials.config')
</body>
</html>
