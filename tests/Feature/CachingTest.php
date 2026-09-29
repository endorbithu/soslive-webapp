<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A statikus oldalaknak reverse proxyban (Varnish, Cloudflare) cache-elhetőnek kell lenniük:
 * nincs Set-Cookie, nincs userfüggő tartalom, public Cache-Control + ETag. A dinamikus /app zóna sosem cache-elhető.
 */
class CachingTest extends TestCase
{
    use RefreshDatabase;

    private const STATIC_PAGES = ['/', '/dashboard', '/settings', '/e/1AbCdEfGhIjKlMnOpQrStUvWxYz0123456789_-abc'];

    public function test_static_pages_are_publicly_cacheable_without_cookies(): void
    {
        config(['soslive.page_cache.max_age' => 60, 'soslive.page_cache.s_maxage' => 3600]);

        foreach (self::STATIC_PAGES as $uri) {
            $response = $this->get($uri)->assertOk();

            $this->assertSame([], $response->headers->getCookies(), "{$uri}: nem küldhet cookie-t");
            $cacheControl = $response->headers->get('Cache-Control');
            $this->assertStringContainsString('public', $cacheControl, $uri);
            $this->assertStringContainsString('max-age=60', $cacheControl, $uri);
            $this->assertStringContainsString('s-maxage=3600', $cacheControl, $uri);
            $this->assertNotEmpty($response->headers->get('ETag'), $uri);
            $this->assertStringNotContainsString('_token', $response->getContent(), $uri);
        }
    }

    public function test_static_html_is_identical_for_guests_and_users(): void
    {
        $user = User::factory()->create(['email' => 'secret-user@example.com']);

        foreach (self::STATIC_PAGES as $uri) {
            $guest = $this->get($uri)->getContent();
            $this->flushSession();
            $loggedIn = $this->actingAs($user)->get($uri)->getContent();

            $this->assertSame($guest, $loggedIn, $uri);
            $this->assertStringNotContainsString('secret-user@example.com', $loggedIn, $uri);
        }
    }

    public function test_static_pages_answer_304_for_matching_etag(): void
    {
        $etag = $this->get('/dashboard')->headers->get('ETag');

        $this->get('/dashboard', ['If-None-Match' => $etag])->assertStatus(304);
    }

    public function test_dynamic_zone_is_never_cacheable_and_cookie_is_scoped(): void
    {
        $response = $this->getJson(route('me'))->assertOk();

        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('private', $cacheControl);

        $cookies = $response->headers->getCookies();
        $this->assertNotEmpty($cookies);
        foreach ($cookies as $cookie) {
            $this->assertSame('/app', $cookie->getPath(), $cookie->getName().' cookie csak az /app alatt élhet');
        }
    }
}
