<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_me_lists_own_and_shared_owners(): void
    {
        $owner = User::factory()->create(['name' => 'Owner Olga']);
        $viewer = User::factory()->create(['email' => 'viewer@example.com', 'name' => 'Viewer Vera']);
        $owner->allowedEmails()->create(['email' => 'viewer@example.com']);
        User::factory()->create(['name' => 'Stranger Steve']);

        $response = $this->actingAs($viewer)->getJson(route('me'))
            ->assertOk()
            ->assertJsonPath('user.email', 'viewer@example.com')
            ->assertJsonPath('owners.0.is_me', true)
            ->assertJsonPath('owners.1.name', 'Owner Olga')
            ->assertJsonCount(2, 'owners');

        $this->assertNotEmpty($response->json('csrf'));
        $this->assertStringNotContainsString('Stranger Steve', $response->getContent());
        $this->assertStringNotContainsString('refresh', $response->getContent());
    }

    public function test_me_contains_read_only_config(): void
    {
        $user = User::factory()->create([
            'notification_emails' => 'mom@example.com',
            'notification_phones' => '+36301234567',
        ]);
        $user->allowedEmails()->create(['email' => 'friend@example.com']);

        $this->actingAs($user)->getJson(route('me'))
            ->assertOk()
            ->assertJsonPath('config.notification_emails', ['mom@example.com'])
            ->assertJsonPath('config.notification_phones', ['+36301234567'])
            ->assertJsonPath('config.allowed_emails', ['friend@example.com'])
            ->assertJsonPath('config.drive_folder_id', $user->drive_folder_id);
    }

    public function test_me_for_guest(): void
    {
        $this->getJson(route('me'))->assertOk()->assertExactJson(['user' => null]);
    }

    public function test_settings_page_is_a_read_only_shell(): void
    {
        $this->get(route('settings'))
            ->assertOk()
            ->assertSee('Ezek a beállítások csak a SOSlive mobil appban módosíthatók.')
            ->assertDontSee('name="notification_emails"', false);

        $user = User::factory()->create(['notification_emails' => 'mom@example.com']);
        $this->actingAs($user)->put('/settings', ['notification_emails' => 'x@example.com'])->assertMethodNotAllowed();
        $this->assertSame('mom@example.com', $user->fresh()->notification_emails);
    }

    public function test_dynamic_endpoints_require_login(): void
    {
        $owner = User::factory()->create();

        $this->getJson(route('events', $owner))->assertUnauthorized();
        $this->post(route('settings.folder'))->assertRedirect(route('home'));
    }
}
