<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventPageTest extends TestCase
{
    use RefreshDatabase;

    private const SHEET_ID = '1AbCdEfGhIjKlMnOpQrStUvWxYz0123456789_-abc';

    public function test_guest_can_open_event_page(): void
    {
        config(['services.google.api_key' => 'public-key']);

        $this->get('/e/'.self::SHEET_ID)
            ->assertOk()
            ->assertSee(self::SHEET_ID)
            ->assertSee('public-key')
            ->assertSee('"owners":[]', false);
    }

    public function test_logged_in_user_gets_visible_owners(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/e/'.self::SHEET_ID)
            ->assertOk()
            ->assertSee('"is_me":true', false);
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
