<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGoogleUser(array $overrides = []): void
    {
        $google = (new SocialiteUser)->map(array_merge([
            'id' => '1234567890',
            'email' => 'Jane@Example.com',
            'name' => 'Jane Doe',
        ], $overrides['user'] ?? []))->setToken('access-1')
            ->setRefreshToken($overrides['refresh'] ?? 'refresh-1')
            ->setExpiresIn(3599)
            ->setApprovedScopes($overrides['scopes'] ?? config('soslive.scopes'));

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($google);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_first_login_creates_user_and_drive_folder(): void
    {
        $this->fakeGoogleUser();
        Http::fake([
            'www.googleapis.com/drive/v3/files?*q=*' => Http::response(['files' => []]),
            'www.googleapis.com/drive/v3/files?fields=id' => Http::response(['id' => 'folder-new']),
        ]);

        $this->get('/auth/google/callback')->assertRedirect(route('dashboard'));

        $user = User::sole();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('jane@example.com', $user->email);
        $this->assertSame('1234567890', $user->google_id);
        $this->assertSame('refresh-1', $user->google_refresh_token);
        $this->assertSame('folder-new', $user->drive_folder_id);
        $this->assertSame(config('soslive.default_max_events'), $user->max_events);
        $this->assertNotNull($user->last_login_at);

        // A refresh token titkosítva van az adatbázisban.
        $this->assertNotSame('refresh-1', DB::table('users')->value('google_refresh_token'));

        Http::assertSent(fn ($r) => $r->method() === 'POST'
            && $r['mimeType'] === 'application/vnd.google-apps.folder'
            && $r['appProperties'] === ['soslive' => 'root']);
    }

    public function test_existing_folder_created_by_mobile_app_is_reused(): void
    {
        $this->fakeGoogleUser();
        Http::fake([
            'www.googleapis.com/drive/v3/files?*' => Http::response(['files' => [['id' => 'folder-mobile']]]),
        ]);

        $this->get('/auth/google/callback');

        $this->assertSame('folder-mobile', User::sole()->drive_folder_id);
        Http::assertNotSent(fn ($r) => $r->method() === 'POST');
    }

    public function test_login_without_drive_scope_is_rejected(): void
    {
        $this->fakeGoogleUser(['scopes' => ['openid', 'email', 'profile']]);

        $this->get('/auth/google/callback')->assertRedirect(route('home'))->assertSessionHas('error');
        $this->assertGuest();
        $this->assertSame(0, User::count());
    }

    public function test_missing_refresh_token_forces_consent(): void
    {
        $this->fakeGoogleUser(['refresh' => '']);

        $this->get('/auth/google/callback')->assertRedirect(route('auth.google', ['consent' => 1]));
        $this->assertGuest();
    }

    public function test_relogin_keeps_stored_refresh_token_and_live_folder(): void
    {
        $user = User::factory()->create(['google_id' => '1234567890', 'google_refresh_token' => 'old-refresh', 'drive_folder_id' => 'folder-1']);
        $this->fakeGoogleUser(['refresh' => '']);
        Http::fake(['www.googleapis.com/drive/v3/files/folder-1*' => Http::response(['id' => 'folder-1', 'trashed' => false])]);

        $this->get('/auth/google/callback')->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertSame('old-refresh', $user->google_refresh_token);
        $this->assertSame('folder-1', $user->drive_folder_id);
    }
}
