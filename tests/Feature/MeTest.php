<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * /app/me: a statikus oldalak egyetlen backend-adata. Csak a belépett user neve, emailje és a CSRF token –
 * események, config, Google token nem (azokat a böngésző a saját Drive-jából olvassa).
 */
class MeTest extends TestCase
{
    use RefreshDatabase;

    public function test_me_for_logged_in_user(): void
    {
        $user = User::factory()->create(['email' => 'jane@example.com', 'name' => 'Jane Doe']);

        $response = $this->actingAs($user)->getJson(route('me'))
            ->assertOk()
            ->assertJsonPath('user', ['name' => 'Jane Doe', 'email' => 'jane@example.com']);

        $this->assertSame(['user', 'csrf'], array_keys($response->json()));
        $this->assertNotEmpty($response->json('csrf'));
    }

    public function test_me_for_guest(): void
    {
        $this->getJson(route('me'))->assertOk()->assertExactJson(['user' => null]);
    }

    public function test_dashboard_and_settings_are_shells_without_user_data(): void
    {
        $this->get(route('dashboard'))->assertOk()->assertSee('id="events"', false);
        $this->get(route('settings'))
            ->assertOk()
            ->assertSee('Ezek a beállítások csak a SOSlive mobil appban módosíthatók.')
            ->assertDontSee('<input', false);
    }

    public function test_removed_endpoints_are_gone(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/app/events/'.$user->id)->assertNotFound();
        $this->actingAs($user)->post('/app/settings/folder')->assertNotFound();
        $this->postJson('/api/session')->assertNotFound();
        $this->getJson('/api/config')->assertNotFound();
        $this->actingAs($user)->put('/settings')->assertMethodNotAllowed();
    }
}
