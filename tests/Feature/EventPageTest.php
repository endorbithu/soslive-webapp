<?php

namespace Tests\Feature;

use App\Models\User;
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

    public function test_invalid_id_is_404(): void
    {
        $this->get('/e/short')->assertNotFound();
    }

    public function test_home_page(): void
    {
        $this->get('/')->assertOk()->assertSee(route('auth.google'));
        $this->actingAs(User::factory()->create())->get('/')->assertRedirect(route('dashboard'));
    }
}
