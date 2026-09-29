<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EventListTest extends TestCase
{
    use RefreshDatabase;

    private const EVENTS = [
        ['id' => 'event-2', 'name' => '2026-09-29 10:00:00.json', 'createdTime' => '2026-09-29T10:00:00Z'],
        ['id' => 'event-1', 'name' => '2026-09-28 08:00:00.json', 'createdTime' => '2026-09-28T08:00:00Z'],
    ];

    private function fakeGoogle(array $events = self::EVENTS, bool $folderTrashed = false): void
    {
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response(['access_token' => 'owner-token', 'expires_in' => 3599]),
            'www.googleapis.com/drive/v3/files?*' => Http::response(['files' => $events]),
            'www.googleapis.com/drive/v3/files/*' => Http::response(['id' => 'folder', 'trashed' => $folderTrashed]),
        ]);
    }

    public function test_owner_gets_event_list_without_token(): void
    {
        $this->fakeGoogle();
        $owner = User::factory()->create(['max_events' => 42]);

        $response = $this->actingAs($owner)->getJson(route('events', $owner))
            ->assertOk()
            ->assertExactJson(['events' => self::EVENTS, 'folder_missing' => false, 'is_owner' => true]);

        $this->assertStringNotContainsString('owner-token', $response->getContent());
        Http::assertSent(fn ($r) => str_contains($r->url(), 'drive/v3/files?')
            && $r->hasHeader('Authorization', 'Bearer owner-token')
            && $r['pageSize'] == 42
            && str_contains($r['q'], "'{$owner->drive_folder_id}' in parents")
            && str_contains($r['q'], "mimeType='application/json'"));
    }

    public function test_list_is_limited_by_config(): void
    {
        $this->fakeGoogle();
        config(['soslive.list_limit' => 10]);
        $owner = User::factory()->create(['max_events' => 500]);

        $this->actingAs($owner)->getJson(route('events', $owner))->assertOk();

        Http::assertSent(fn ($r) => str_contains($r->url(), 'drive/v3/files?') && $r['pageSize'] == 10);
    }

    public function test_allowed_viewer_gets_owner_events(): void
    {
        $this->fakeGoogle();
        $owner = User::factory()->create();
        $viewer = User::factory()->create(['email' => 'viewer@example.com']);
        $owner->allowedEmails()->create(['email' => 'viewer@example.com']);

        $this->actingAs($viewer)->getJson(route('events', $owner))
            ->assertOk()
            ->assertJson(['events' => self::EVENTS, 'is_owner' => false]);
    }

    public function test_stranger_is_forbidden(): void
    {
        $this->fakeGoogle();
        $owner = User::factory()->create();

        $this->actingAs(User::factory()->create())->getJson(route('events', $owner))->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_guest_is_rejected(): void
    {
        $this->getJson(route('events', User::factory()->create()))->assertUnauthorized();
    }

    public function test_deleted_folder_is_reported(): void
    {
        $this->fakeGoogle(events: [], folderTrashed: true);
        $owner = User::factory()->create();

        $this->actingAs($owner)->getJson(route('events', $owner))
            ->assertOk()
            ->assertJson(['events' => [], 'folder_missing' => true]);
    }

    public function test_empty_but_live_folder_is_not_missing(): void
    {
        $this->fakeGoogle(events: []);
        $owner = User::factory()->create();

        $this->actingAs($owner)->getJson(route('events', $owner))
            ->assertOk()
            ->assertJson(['events' => [], 'folder_missing' => false]);
    }

    public function test_access_token_is_cached_between_requests(): void
    {
        $this->fakeGoogle();
        $owner = User::factory()->create();

        $this->actingAs($owner)->getJson(route('events', $owner))->assertOk();
        $this->actingAs($owner)->getJson(route('events', $owner))->assertOk();

        Http::assertSentCount(3); // 1 token + 2 lista
    }

    public function test_revoked_refresh_token_requires_reauth(): void
    {
        Http::fake(['oauth2.googleapis.com/token' => Http::response(['error' => 'invalid_grant'], 400)]);
        $owner = User::factory()->create();
        $viewer = User::factory()->create(['email' => 'viewer@example.com']);
        $owner->allowedEmails()->create(['email' => 'viewer@example.com']);

        $this->actingAs($viewer)->getJson(route('events', $owner))
            ->assertStatus(409)->assertJson(['error' => 'owner_reauth']);
        $this->assertNull($owner->fresh()->google_refresh_token);

        $this->actingAs($owner)->getJson(route('events', $owner))
            ->assertUnauthorized()->assertJson(['error' => 'reauth']);
    }
}
