<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A frontend build lépés nélkül, natív ES modulokkal fut: az import map minden modult verzióval (?v=filemtime) tölt,
 * hogy deploy után a böngésző / proxy cache ne adjon vissza régi modult.
 */
class FrontendTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_load_versioned_modules_via_import_map(): void
    {
        foreach (['/', '/dashboard', '/settings', '/e/1AbCdEfGhIjKlMnOpQrStUvWxYz0123456789_-abc'] as $uri) {
            $html = $this->get($uri)->assertOk()->getContent();

            preg_match('#<script type="importmap">(.*?)</script>#s', $html, $m);
            $this->assertNotEmpty($m, $uri);
            $imports = json_decode($m[1], true)['imports'];

            $this->assertMatchesRegularExpression('#^/js/lib/dom\.js\?v=\d+$#', $imports['soslive/lib/dom.js'], $uri);
            $this->assertArrayHasKey('soslive/pages/event.js', $imports, $uri);
            $this->assertStringContainsString('<script type="module" src="'.$imports['soslive/app.js'].'"></script>', $html, $uri);
            $this->assertStringContainsString('href="/favicon.svg"', $html, $uri);
            $this->assertStringNotContainsString('soslive.js', $html, $uri);
        }
    }

    public function test_every_module_import_is_in_the_import_map(): void
    {
        $html = $this->get('/')->getContent();
        preg_match('#<script type="importmap">(.*?)</script>#s', $html, $m);
        $imports = json_decode($m[1], true)['imports'];

        foreach (glob(public_path('js/{,*/}*.js'), GLOB_BRACE) as $file) {
            preg_match_all("#from '([^']+)'#", file_get_contents($file), $found);
            foreach ($found[1] as $specifier) {
                $this->assertArrayHasKey($specifier, $imports, basename($file).": {$specifier}");
            }
        }
    }
}
