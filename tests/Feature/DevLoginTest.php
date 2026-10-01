<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevLoginTest extends TestCase
{
    use RefreshDatabase;

    private function asProduction(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
    }

    public function test_dev_login_creates_user_and_logs_in(): void
    {
        $this->get(route('auth.dev'))->assertOk()->assertSee('Teszt belépés');

        $this->post(route('auth.dev'), ['email' => 'Teszt@Example.com'])
            ->assertRedirect(route('dashboard'));

        $user = User::sole();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('teszt@example.com', $user->email);
        $this->assertSame('dev:teszt@example.com', $user->google_id);
        $this->assertNotNull($user->last_login_at);
    }

    public function test_dev_login_reuses_existing_user(): void
    {
        $user = User::factory()->create(['email' => 'jane@example.com', 'google_id' => '123', 'name' => 'Jane']);

        $this->post(route('auth.dev'), ['email' => 'jane@example.com'])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame('123', $user->fresh()->google_id);
        $this->assertSame(1, User::count());
    }

    public function test_dev_login_validates_email(): void
    {
        $this->post(route('auth.dev'), ['email' => 'nem-email'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_dev_login_is_not_available_in_production(): void
    {
        $this->asProduction();
        // Production alatt a CSRF ellenőrzés is aktív; kikapcsoljuk, hogy a kérés a controller 404-es védelméig érjen.
        $this->withoutMiddleware(PreventRequestForgery::class);

        $this->get(route('auth.dev'))->assertNotFound();
        $this->post(route('auth.dev'), ['email' => 'teszt@example.com'])->assertNotFound();
        $this->assertGuest();
        $this->assertSame(0, User::count());
    }

    public function test_dev_login_link_only_outside_production(): void
    {
        $this->get('/')->assertSee('Teszt belépés Google nélkül');

        $this->asProduction();
        $this->get('/')->assertDontSee('Teszt belépés');
    }
}
