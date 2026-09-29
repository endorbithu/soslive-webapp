<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_syncs_allowed_emails_with_notification_emails(): void
    {
        $user = User::factory()->create(['email' => 'me@example.com']);
        $user->allowedEmails()->create(['email' => 'old@example.com']);

        $this->actingAs($user)->put(route('settings.update'), [
            'notification_emails' => 'Mom@Example.com, dad@example.com',
            'notification_phones' => '+36 20 123 4567, +36301234567',
            'allowed_emails' => "friend@example.com\nme@example.com\nmom@example.com",
        ])->assertRedirect()->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('mom@example.com, dad@example.com', $user->notification_emails);
        $this->assertSame('+36 20 123 4567, +36301234567', $user->notification_phones);
        $this->assertEqualsCanonicalizing(
            ['friend@example.com', 'mom@example.com', 'dad@example.com'],
            $user->allowedEmails()->pluck('email')->all()
        );
    }

    public function test_invalid_values_are_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('settings.update'), [
            'notification_emails' => 'not-an-email',
            'notification_phones' => 'abc',
            'allowed_emails' => 'also bad',
        ])->assertSessionHasErrors(['notification_emails.0', 'notification_phones.0', 'allowed_emails.0']);

        $this->actingAs($user)->put(route('settings.update'), [
            // Egyenként érvényes címek, de összefűzve 255 karakternél hosszabbak.
            'notification_emails' => implode(',', array_map(fn ($i) => "user{$i}@".str_repeat('x', 50).'.hu', range(1, 5))),
        ])->assertSessionHasErrors('notification_emails');
    }

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

    public function test_settings_page_shows_current_values(): void
    {
        $user = User::factory()->create(['notification_phones' => '+36301234567']);
        $user->allowedEmails()->create(['email' => 'friend@example.com']);

        $this->actingAs($user)->get(route('settings'))
            ->assertOk()
            ->assertSee('+36301234567')
            ->assertSee('friend@example.com')
            ->assertSee($user->drive_folder_id);
    }

    public function test_guest_is_redirected_home(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('home'));
        $this->get(route('settings'))->assertRedirect(route('home'));
    }
}
