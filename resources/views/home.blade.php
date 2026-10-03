@extends('layouts.app')

@section('page', 'home')

@section('content')
    <section class="hero">
        <h1>Ha baj van, a szeretteid azonnal látják.</h1>
        <p class="lead">A SOSlive mobil app élő videót, helyzetet és üzeneteket oszt meg az általad megadott
            emberekkel. Az eseményeid a saját Google Drive-odban tárolódnak; a SOSlive csak az általa létrehozott
            fájlokhoz fér hozzá.</p>
        <p class="actions">
            <a id="home-cta" class="button" href="{{ route('auth.google', [], false) }}">Belépés Google-fiókkal</a>
            @unless (app()->isProduction())
                <a class="button secondary" href="{{ route('auth.dev', [], false) }}">Teszt belépés Google nélkül</a>
            @endunless
        </p>
    </section>

    <ol class="steps">
        <li class="card">
            <h2>Rögzítés</h2>
            <p>Vészhelyzetben a mobil app elindítja a streamet, és 30 másodpercenként elküldi a pozíciódat.</p>
        </li>
        <li class="card">
            <h2>A te Drive-odon</h2>
            <p>Minden esemény egy fájl a saját Google Drive-od SOSlive mappájában. Bármikor törölheted.</p>
        </li>
        <li class="card">
            <h2>Értesítés</h2>
            <p>Akiket megadtál, emailben / SMS-ben kapják meg az esemény linkjét, és SMS-ben válaszolhatnak.</p>
        </li>
    </ol>

    @include('partials.demo-events')
@endsection
