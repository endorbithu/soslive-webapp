<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TokenTest extends TestCase
{
    use RefreshDatabase;

    private function fakeTokenEndpoint(): void
    {
        Http::fake(['oauth2.googleapis.com/token' => Http::response(['access_token' => 'fresh-token', 'expires_in' => 3599])]);
    }

    public function test_owner_gets_own_token(): void
    {
        $this->fakeTokenEndpoint();
        $owner = User::factory()->create(['max_events' => 42]);

        $this->actingAs($owner)->getJson(route('token', $owner))
            ->assertOk()
            ->assertJson([
                'access_token' => 'fresh-token',
                'folder_id' => $owner->drive_folder_id,
                'max_events' => 42,
                'is_owner' => true,
            ]);
    }

    public function test_token_is_cached_between_requests(): void
    {
        $this->fakeTokenEndpoint();
        $owner = User::factory()->create();

        $this->actingAs($owner)->getJson(route('token', $owner))->assertOk();
        $this->actingAs($owner)->getJson(route('token', $owner))->assertOk();

        Http::assertSentCount(1);
    }

    public function test_allowed_viewer_gets_owner_token(): void
    {
        $this->fakeTokenEndpoint();
        $owner = User::factory()->create();
        $viewer = User::factory()->create(['email' => 'viewer@example.com']);
        $owner->allowedEmails()->create(['email' => 'viewer@example.com']);

        $this->actingAs($viewer)->getJson(route('token', $owner))
            ->assertOk()
            ->assertJson(['access_token' => 'fresh-token', 'is_owner' => false]);
    }

    public function test_stranger_is_forbidden(): void
    {
        $this->fakeTokenEndpoint();
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->getJson(route('token', $owner))->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_guest_is_rejected(): void
    {
        $this->fakeTokenEndpoint();
        $owner = User::factory()->create();

        $this->getJson(route('token', $owner))->assertUnauthorized();
    }

    public function test_revoked_refresh_token_requires_reauth(): void
    {
        Http::fake(['oauth2.googleapis.com/token' => Http::response(['error' => 'invalid_grant'], 400)]);
        $owner = User::factory()->create();
        $viewer = User::factory()->create(['email' => 'viewer@example.com']);
        $owner->allowedEmails()->create(['email' => 'viewer@example.com']);

        $this->actingAs($viewer)->getJson(route('token', $owner))
            ->assertStatus(409)->assertJson(['error' => 'owner_reauth']);
        $this->assertNull($owner->fresh()->google_refresh_token);

        $this->actingAs($owner)->getJson(route('token', $owner))
            ->assertUnauthorized()->assertJson(['error' => 'reauth']);
    }
}
