<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventPageTest extends TestCase
{
    use RefreshDatabase;

    private const FILE_ID = '1AbCdEfGhIjKlMnOpQrStUvWxYz0123456789_-abc';

    public function test_guest_can_open_event_page(): void
    {
        config(['services.google.api_key' => 'public-key']);

        $this->get('/e/'.self::FILE_ID)
            ->assertOk()
            ->assertSee(self::FILE_ID)
            ->assertSee('public-key')
            ->assertSee('SMS-ben válaszolhatsz arra a számra, ahonnan az értesítést kaptad.')
            ->assertDontSee('docs.google.com')
            ->assertDontSee('id="chat"', false)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_event_page_has_route_map(): void
    {
        config(['soslive.map.tile_url' => 'https://tiles.example.com/{z}/{x}/{y}.png']);

        $this->get('/e/'.self::FILE_ID)
            ->assertOk()
            ->assertSee('<div id="map" class="map" hidden></div>', false)
            ->assertSee('"mapTileUrl":"https:\\/\\/tiles.example.com\\/{z}\\/{x}\\/{y}.png"', false)
            ->assertSee('"mapAttribution":', false);

        // A Leaflet saját példány (nincs külső CDN); az útvonala a lib/config.js-ben van.
        foreach (['leaflet.js', 'leaflet.css', 'LICENSE'] as $file) {
            $this->assertFileExists(public_path('vendor/leaflet/1.9.4/'.$file));
        }
        $this->assertStringContainsString("'/vendor/leaflet/1.9.4/leaflet'", file_get_contents(public_path('js/lib/config.js')));
    }

    public function test_picker_app_id_in_config(): void
    {
        config(['services.google.app_id' => '123456789012']);

        $this->get('/dashboard')->assertOk()->assertSee('"googleAppId":"123456789012"', false);
    }

    public function test_dashboard_has_shared_events_section(): void
    {
        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Velem megosztott események')
            ->assertSee('id="shared-add"', false)
            ->assertSee('id="shared-list"', false);
    }

    public function test_settings_shows_viewers_card(): void
    {
        $this->get('/settings')
            ->assertOk()
            ->assertSee('Kik látják az eseményeidet')
            ->assertSee('id="cfg-viewers"', false);
    }

    public function test_invalid_id_is_404(): void
    {
        $this->get('/e/short')->assertNotFound();
    }

    public function test_home_page(): void
    {
        $this->get('/')->assertOk()->assertSee(route('auth.google', [], false));
    }
}
