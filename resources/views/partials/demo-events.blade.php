{{-- Demó események a végoldal Google nélküli teszteléséhez – csak nem production környezetben (App\Support\DemoEvents). --}}
@unless (app()->isProduction())
    <section id="demo-events">
        <h2>Demó események (teszt adatok)</h2>
        <p class="muted">Beépített adatok, Google nélkül – csak {{ app()->environment() }} környezetben látszik.</p>
        <ul>
            @foreach (\App\Support\DemoEvents::all() as $demo)
                <li><a href="{{ route('event', $demo['id'], false) }}">{{ $demo['label'] }}</a></li>
            @endforeach
        </ul>
    </section>
@endunless
