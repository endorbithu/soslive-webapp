<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_sees_shared_owner_on_dashboard(): void
    {
        $owner = User::factory()->create(['name' => 'Owner Olga']);
        $viewer = User::factory()->create(['email' => 'viewer@example.com']);
        $owner->allowedEmails()->create(['email' => 'viewer@example.com']);
        User::factory()->create(['name' => 'Stranger Steve']);

        $this->actingAs($viewer)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Saját eseményeim')
            ->assertSee('Owner Olga')
            ->assertDontSee('Stranger Steve');
    }

    public function test_settings_page_is_read_only(): void
    {
        $user = User::factory()->create([
            'notification_emails' => 'mom@example.com',
            'notification_phones' => '+36301234567',
        ]);
        $user->allowedEmails()->create(['email' => 'friend@example.com']);

        $this->actingAs($user)->get(route('settings'))
            ->assertOk()
            ->assertSee('Ezek a beállítások csak a SOSlive mobil appban módosíthatók.')
            ->assertSee('mom@example.com')
            ->assertSee('+36301234567')
            ->assertSee('friend@example.com')
            ->assertSee($user->drive_folder_id)
            ->assertDontSee('name="notification_emails"', false);

        $this->actingAs($user)->put('/settings', ['notification_emails' => 'x@example.com'])->assertMethodNotAllowed();
        $this->assertSame('mom@example.com', $user->fresh()->notification_emails);
    }

    public function test_guest_is_redirected_home(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('home'));
        $this->get(route('settings'))->assertRedirect(route('home'));
    }
}
