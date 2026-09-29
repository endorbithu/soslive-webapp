<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\GoogleDrive;
use App\Services\GoogleIdTokenVerifier;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    private const CLIENT_ID = 'android-client.apps.googleusercontent.com';

    private static string $privateKey = '';

    private static array $jwks = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($key, self::$privateKey);
        $rsa = openssl_pkey_get_details($key)['rsa'];
        $b64 = fn (string $bin) => rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
        self::$jwks = ['keys' => [['kty' => 'RSA', 'alg' => 'RS256', 'use' => 'sig', 'kid' => 'k1', 'n' => $b64($rsa['n']), 'e' => $b64($rsa['e'])]]];
    }

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.google.mobile_client_ids' => [self::CLIENT_ID, 'ios-client.apps.googleusercontent.com']]);
    }

    /**
     * @param  array<string, string|\Closure>  $extra  további Http::fake szabályok
     */
    private function fakeGoogle(array $extra = []): void
    {
        Http::fake($extra + [
            GoogleIdTokenVerifier::CERTS_URL => Http::response(self::$jwks, 200, ['Cache-Control' => 'public, max-age=3600']),
        ]);
    }

    private function idToken(array $claims = [], ?string $privateKey = null): string
    {
        return JWT::encode($claims + [
            'iss' => 'https://accounts.google.com',
            'aud' => self::CLIENT_ID,
            'sub' => '1234567890',
            'email' => 'jane@example.com',
            'email_verified' => true,
            'name' => 'Jane Doe',
            'iat' => time(),
            'exp' => time() + 3600,
        ], $privateKey ?? self::$privateKey, 'RS256', 'k1');
    }

    private function api(string $method, string $uri, array $data = [], ?string $token = null)
    {
        return $this->withToken($token ?? $this->idToken())->json($method, $uri, $data);
    }

    // ---- Hitelesítés ----------------------------------------------------------------------------

    public function test_missing_token_is_rejected(): void
    {
        $this->getJson('/api/config')->assertUnauthorized()->assertJson(['error' => 'missing_token']);
    }

    public function test_invalid_tokens_are_rejected(): void
    {
        $this->fakeGoogle();
        $otherKey = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($otherKey, $otherPem);

        $invalid = [
            'rossz aláírás' => $this->idToken([], $otherPem),
            'rossz aud' => $this->idToken(['aud' => 'someone-else']),
            'rossz iss' => $this->idToken(['iss' => 'https://evil.example.com']),
            'lejárt' => $this->idToken(['exp' => time() - 3600, 'iat' => time() - 7200]),
            'nem ellenőrzött email' => $this->idToken(['email_verified' => false]),
            'szemét' => 'not-a-jwt',
        ];
        foreach ($invalid as $case => $token) {
            $this->api('GET', '/api/config', [], $token)->assertUnauthorized();
        }
    }

    public function test_google_certs_are_cached(): void
    {
        $this->fakeGoogle();
        User::factory()->create(['google_id' => '1234567890']);

        $this->api('GET', '/api/config')->assertOk();
        $this->api('GET', '/api/config')->assertOk();

        Http::assertSentCount(1);
    }

    // ---- Session --------------------------------------------------------------------------------

    public function test_new_user_without_server_auth_code_gets_409(): void
    {
        $this->fakeGoogle();

        $this->api('POST', '/api/session')->assertStatus(409)->assertJson(['error' => 'server_auth_code_required']);
        $this->assertSame(0, User::count());
    }

    public function test_new_user_session_with_server_auth_code(): void
    {
        $this->fakeGoogle([
            GoogleDrive::TOKEN_URL => Http::response([
                'access_token' => 'access-1', 'refresh_token' => 'refresh-1', 'expires_in' => 3599,
                'scope' => 'openid https://www.googleapis.com/auth/userinfo.email '.GoogleDrive::DRIVE_SCOPE,
            ]),
            'www.googleapis.com/drive/v3/files?fields=id' => Http::response(['id' => 'folder-new']),
            'www.googleapis.com/drive/v3/files?*' => Http::response(['files' => []]),
        ]);

        $this->api('POST', '/api/session', ['server_auth_code' => '4/abc'])
            ->assertOk()
            ->assertExactJson([
                'email' => 'jane@example.com',
                'name' => 'Jane Doe',
                'drive_folder_id' => 'folder-new',
                'max_events' => config('soslive.default_max_events'),
                'notification_emails' => [],
                'notification_phones' => [],
                'allowed_emails' => [],
            ]);

        $user = User::sole();
        $this->assertSame('1234567890', $user->google_id);
        $this->assertSame('refresh-1', $user->google_refresh_token);
        Http::assertSent(fn ($r) => $r->url() === GoogleDrive::TOKEN_URL
            && $r['grant_type'] === 'authorization_code' && $r['code'] === '4/abc');
    }

    public function test_server_auth_code_without_drive_scope_is_rejected(): void
    {
        $this->fakeGoogle([
            GoogleDrive::TOKEN_URL => Http::response(['access_token' => 'a', 'refresh_token' => 'r', 'expires_in' => 3599, 'scope' => 'openid email']),
        ]);

        $this->api('POST', '/api/session', ['server_auth_code' => '4/abc'])
            ->assertStatus(422)->assertJson(['error' => 'drive_scope_missing']);
        $this->assertSame(0, User::count());
    }

    public function test_invalid_server_auth_code_is_rejected(): void
    {
        $this->fakeGoogle([GoogleDrive::TOKEN_URL => Http::response(['error' => 'invalid_grant'], 400)]);

        $this->api('POST', '/api/session', ['server_auth_code' => 'bad'])
            ->assertStatus(422)->assertJson(['error' => 'invalid_server_auth_code']);
    }

    public function test_existing_user_session_without_code(): void
    {
        $user = User::factory()->create(['google_id' => '1234567890', 'drive_folder_id' => 'folder-1', 'name' => 'Old Name']);
        $this->fakeGoogle([
            GoogleDrive::TOKEN_URL => Http::response(['access_token' => 'access-2', 'expires_in' => 3599]),
            'www.googleapis.com/drive/v3/files/folder-1*' => Http::response(['id' => 'folder-1', 'trashed' => false]),
        ]);

        $this->api('POST', '/api/session')->assertOk()->assertJson(['drive_folder_id' => 'folder-1', 'name' => 'Jane Doe']);

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    // ---- Config ---------------------------------------------------------------------------------

    public function test_config_requires_session(): void
    {
        $this->fakeGoogle();

        $this->api('GET', '/api/config')->assertStatus(409)->assertJson(['error' => 'session_required']);
        $this->api('PUT', '/api/config', ['notification_emails' => []])->assertStatus(409);
    }

    public function test_get_config(): void
    {
        $this->fakeGoogle();
        $user = User::factory()->create([
            'google_id' => '1234567890',
            'max_events' => 42,
            'notification_emails' => 'mom@example.com',
            'notification_phones' => '+36 20 123 4567, +36301234567',
        ]);
        $user->allowedEmails()->createMany([['email' => 'mom@example.com'], ['email' => 'friend@example.com']]);

        $this->api('GET', '/api/config')->assertOk()->assertJson([
            'max_events' => 42,
            'drive_folder_id' => $user->drive_folder_id,
            'notification_emails' => ['mom@example.com'],
            'notification_phones' => ['+36 20 123 4567', '+36301234567'],
            'allowed_emails' => ['friend@example.com', 'mom@example.com'],
        ]);
    }

    public function test_update_config_syncs_allowed_emails(): void
    {
        $this->fakeGoogle();
        $user = User::factory()->create(['google_id' => '1234567890', 'email' => 'jane@example.com']);
        $user->allowedEmails()->create(['email' => 'old@example.com']);

        $this->api('PUT', '/api/config', [
            'notification_emails' => ['Mom@Example.com', 'dad@example.com'],
            'notification_phones' => ['+36 20 123 4567', '+36301234567'],
            'allowed_emails' => ['friend@example.com', 'jane@example.com', 'mom@example.com'],
        ])->assertOk()->assertJson([
            'notification_emails' => ['mom@example.com', 'dad@example.com'],
            'notification_phones' => ['+36 20 123 4567', '+36301234567'],
            'allowed_emails' => ['dad@example.com', 'friend@example.com', 'mom@example.com'],
        ]);

        $user->refresh();
        $this->assertSame('mom@example.com, dad@example.com', $user->notification_emails);
        $this->assertSame('+36 20 123 4567, +36301234567', $user->notification_phones);
    }

    public function test_update_config_accepts_comma_separated_strings(): void
    {
        $this->fakeGoogle();
        User::factory()->create(['google_id' => '1234567890']);

        $this->api('PUT', '/api/config', ['notification_emails' => 'a@example.com, b@example.com'])
            ->assertOk()
            ->assertJson(['notification_emails' => ['a@example.com', 'b@example.com']]);
    }

    public function test_update_config_validation(): void
    {
        $this->fakeGoogle();
        User::factory()->create(['google_id' => '1234567890']);

        $this->api('PUT', '/api/config', [
            'notification_emails' => ['not-an-email'],
            'notification_phones' => ['abc'],
            'allowed_emails' => ['also bad'],
        ])->assertUnprocessable()->assertJsonValidationErrors(['notification_emails.0', 'notification_phones.0', 'allowed_emails.0']);

        $this->api('PUT', '/api/config', ['notification_emails' => [['nested']]])
            ->assertUnprocessable()->assertJsonValidationErrors('notification_emails');

        // Egyenként érvényes címek, de összefűzve 255 karakternél hosszabbak.
        $this->api('PUT', '/api/config', [
            'notification_emails' => array_map(fn ($i) => "user{$i}@".str_repeat('x', 50).'.hu', range(1, 5)),
        ])->assertUnprocessable()->assertJsonValidationErrors('notification_emails');
    }
}
