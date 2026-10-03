{{-- Demó események a végoldal Google nélküli teszteléséhez – csak nem production környezetben (App\Support\DemoEvents). --}}
@unless (app()->isProduction())
    <section id="demo-events" class="card dev">
        <h2>Demó események (teszt adatok)</h2>
        <p class="muted">Beépített adatok, Google nélkül – csak {{ app()->environment() }} környezetben látszik.</p>
        <ul class="events">
            @foreach (\App\Support\DemoEvents::all() as $demo)
                <li>
                    <a class="event-link" href="{{ route('event', $demo['id'], false) }}">
                        <span class="event-name">{{ $demo['label'] }}</span>
                        <span class="event-meta">{{ $demo['id'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>
@endunless
