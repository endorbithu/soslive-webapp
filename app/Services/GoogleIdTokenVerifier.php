<?php

namespace App\Services;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use UnexpectedValueException;

/**
 * A mobil appok Google ID tokenjének ellenőrzése (aláírás a Google nyilvános kulcsaival, kiállító, címzett, lejárat).
 */
class GoogleIdTokenVerifier
{
    public const CERTS_URL = 'https://www.googleapis.com/oauth2/v3/certs';

    private const ISSUERS = ['accounts.google.com', 'https://accounts.google.com'];

    private const CACHE_KEY = 'google-id-token-certs';

    /**
     * @return array{sub: string, email: string, name: ?string}
     *
     * @throws UnexpectedValueException érvénytelen token esetén
     */
    public function verify(string $idToken): array
    {
        $claims = $this->decode($idToken);

        $audiences = config('services.google.mobile_client_ids');
        if (! in_array($claims->iss ?? null, self::ISSUERS, true)) {
            throw new UnexpectedValueException('Invalid issuer');
        }
        if (! $audiences || ! in_array($claims->aud ?? null, $audiences, true)) {
            throw new UnexpectedValueException('Invalid audience');
        }
        if (empty($claims->sub) || empty($claims->email) || ($claims->email_verified ?? false) !== true) {
            throw new UnexpectedValueException('Missing or unverified email');
        }

        return ['sub' => (string) $claims->sub, 'email' => (string) $claims->email, 'name' => $claims->name ?? null];
    }

    private function decode(string $idToken): object
    {
        JWT::$leeway = 60;

        try {
            return JWT::decode($idToken, JWK::parseKeySet($this->certs(), 'RS256'));
        } catch (UnexpectedValueException $e) {
            // Ismeretlen kulcs (kid): a Google időnként cserél kulcsot – friss kulcsokkal egyszer újrapróbáljuk,
            // de legfeljebb 5 percenként töltjük újra (hamis kid-del ne lehessen folyamatos letöltést kiváltani).
            if (! str_contains($e->getMessage(), '"kid" invalid') || ! Cache::add(self::CACHE_KEY.':refreshed', true, 300)) {
                throw $e;
            }
            Cache::forget(self::CACHE_KEY);

            return JWT::decode($idToken, JWK::parseKeySet($this->certs(), 'RS256'));
        }
    }

    /**
     * @return array{keys: array<int, array<string, string>>}
     */
    private function certs(): array
    {
        $cached = Cache::get(self::CACHE_KEY);
        if ($cached) {
            return $cached;
        }

        $response = Http::get(self::CERTS_URL)->throw();
        preg_match('/max-age=(\d+)/', (string) $response->header('Cache-Control'), $m);
        Cache::put(self::CACHE_KEY, $response->json(), (int) ($m[1] ?? 3600));

        return $response->json();
    }
}
