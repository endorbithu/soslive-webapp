<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Adatvédelmi tájékoztató és felhasználási feltételek: statikus PDF-ek a public/legal alatt (a Google OAuth consent
 * screen is ezekre hivatkozik), linkek minden statikus oldal láblécében.
 */
class LegalTest extends TestCase
{
    use RefreshDatabase;

    private const PDFS = ['adatvedelem', 'felhasznalasi-feltetelek', 'privacy-policy', 'terms-of-service'];

    public function test_pdfs_exist(): void
    {
        foreach (self::PDFS as $name) {
            $path = public_path("legal/{$name}.pdf");
            $this->assertFileExists($path);
            $this->assertStringStartsWith('%PDF', file_get_contents($path, length: 8), $name);
        }
    }

    public function test_sources_have_no_placeholders(): void
    {
        foreach (glob(resource_path('legal/*.html')) as $file) {
            $this->assertDoesNotMatchRegularExpression('/class="todo"|\[(SZÉKHELY|TÁRHELYSZOLGÁLTATÓ|REGISTERED ADDRESS|HOSTING PROVIDER)\]/u', file_get_contents($file), basename($file));
        }
    }

    public function test_static_pages_link_to_legal_documents(): void
    {
        foreach (['/', '/dashboard', '/settings', '/e/1AbCdEfGhIjKlMnOpQrStUvWxYz0123456789_-abc'] as $uri) {
            $response = $this->get($uri)->assertOk();
            foreach (self::PDFS as $name) {
                $response->assertSee("href=\"/legal/{$name}.pdf\"", false);
            }
        }
    }

    public function test_home_page_mentions_acceptance(): void
    {
        $this->get('/')->assertSee('A belépéssel elfogadod a', false);
    }
}
