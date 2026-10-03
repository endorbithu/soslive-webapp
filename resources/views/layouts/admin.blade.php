{{-- Admin layout: dinamikus (session, CSRF), az /app/admin alatt; nem cache-elődik. --}}
<!doctype html>
<html lang="hu">
<head>
    @include('partials.head')
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'SOSlive admin')</title>
</head>
<body>
<header class="top">
    <div class="container top-inner">
        @include('partials.brand', ['href' => route('admin.users.index'), 'label' => 'SOSlive admin'])
        <nav>
            @auth('admin')
                <a href="{{ route('admin.users.index') }}">Userek</a>
                <form method="post" action="{{ route('admin.logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="link">Kilépés</button>
                </form>
            @endauth
        </nav>
    </div>
</header>
<main class="container">
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
