<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): void
    {
        DB::table('admins')->insert([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('correct-horse-battery'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function loginAdmin(): void
    {
        $this->createAdmin();
        $this->post(route('admin.login'), ['email' => 'admin@example.com', 'password' => 'correct-horse-battery'])
            ->assertRedirect(route('admin.users.index'));
    }

    public function test_guest_and_regular_user_cannot_access_admin(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('admin.login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.users.index'))->assertRedirect(route('admin.login'));
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->createAdmin();

        $this->post(route('admin.login'), ['email' => 'admin@example.com', 'password' => 'nope'])
            ->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_admin_can_list_and_delete_users(): void
    {
        $this->loginAdmin();
        $user = User::factory()->create(['email' => 'user@example.com']);

        $this->get(route('admin.users.index'))->assertOk()->assertSee('user@example.com');
        $this->get(route('admin.users.index', ['q' => 'nobody']))->assertOk()->assertDontSee('user@example.com');

        $this->delete(route('admin.users.destroy', $user))->assertRedirect(route('admin.users.index'));
        $this->assertModelMissing($user);
    }

    public function test_create_admin_command(): void
    {
        $this->artisan('admin:create', ['email' => 'Boss@Example.com'])
            ->expectsQuestion('Jelszó (min. 12 karakter)', 'a-very-long-password')
            ->assertSuccessful();

        $this->assertTrue(Hash::check('a-very-long-password', DB::table('admins')->where('email', 'boss@example.com')->value('password')));
    }
}
