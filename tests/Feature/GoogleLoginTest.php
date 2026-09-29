<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // A backend nem hívhat Google API-t: minden kimenő HTTP kérés hibát okoz.
        Http::preventStrayRequests();
    }

    private function fakeGoogleUser(array $overrides = []): void
    {
        $google = (new SocialiteUser)->map(array_merge([
            'id' => '1234567890',
            'email' => 'Jane@Example.com',
            'name' => 'Jane Doe',
        ], $overrides['user'] ?? []))->setToken('access-1')
            ->setExpiresIn(3599)
            ->setApprovedScopes($overrides['scopes'] ?? config('soslive.scopes'));

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($google);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_first_login_creates_user_without_storing_google_tokens(): void
    {
        $this->fakeGoogleUser();

        $this->get(route('auth.google.callback'))->assertRedirect(route('dashboard'));

        $user = User::sole();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('jane@example.com', $user->email);
        $this->assertSame('1234567890', $user->google_id);
        $this->assertSame('Jane Doe', $user->name);
        $this->assertNotNull($user->last_login_at);
        $this->assertSame(
            ['id', 'email', 'google_id', 'name', 'last_login_at', 'remember_token', 'created_at', 'updated_at'],
            array_keys($user->getAttributes() + ['remember_token' => null, 'created_at' => null, 'updated_at' => null]),
        );
    }

    public function test_relogin_updates_existing_user(): void
    {
        $user = User::factory()->create(['google_id' => '1234567890', 'name' => 'Old Name']);
        $this->fakeGoogleUser();

        $this->get(route('auth.google.callback'))->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertSame('Jane Doe', $user->name);
        $this->assertNotNull($user->last_login_at);
        $this->assertSame(1, User::count());
    }

    public function test_login_without_drive_scope_is_rejected(): void
    {
        $this->fakeGoogleUser(['scopes' => ['openid', 'email', 'profile']]);

        $this->get(route('auth.google.callback'))->assertRedirect(route('home', ['msg' => 'drive_scope']));
        $this->assertGuest();
        $this->assertSame(0, User::count());
    }

    public function test_failed_google_login(): void
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andThrow(new \RuntimeException('invalid state'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get(route('auth.google.callback'))->assertRedirect(route('home', ['msg' => 'login_failed']));
        $this->assertGuest();
    }

    public function test_logout(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
