<?php

namespace App\Services;

use App\Exceptions\GoogleReauthRequired;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

/**
 * A backend egyetlen Google-kapcsolata: access token a refresh tokenből, és a user SOSlive mappájának kezelése.
 * Esemény-adatot a backend nem olvas és nem ír – azt a böngésző végzi.
 */
class GoogleDrive
{
    public const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    public const FILES_URL = 'https://www.googleapis.com/drive/v3/files';

    public const FOLDER_MIME = 'application/vnd.google-apps.folder';

    /**
     * Érvényes access token a user nevében: ['access_token' => ..., 'expires_at' => unix ts].
     *
     * @return array{access_token: string, expires_at: int}
     */
    public function accessTokenFor(User $user): array
    {
        $cached = Cache::get($this->cacheKey($user));
        if ($cached) {
            $token = json_decode(Crypt::decryptString($cached), true);
            if (time() < $token['expires_at'] - 300) {
                return $token;
            }
        }

        if (! $user->google_refresh_token) {
            throw new GoogleReauthRequired;
        }

        $response = Http::asForm()->post(self::TOKEN_URL, [
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'refresh_token' => $user->google_refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if ($response->status() === 400 && $response->json('error') === 'invalid_grant') {
            // Visszavont hozzáférés vagy lejárt refresh token.
            $user->forceFill(['google_refresh_token' => null])->save();
            throw new GoogleReauthRequired;
        }
        $response->throw();

        return $this->rememberAccessToken($user, $response->json('access_token'), (int) $response->json('expires_in', 3600));
    }

    /**
     * @return array{access_token: string, expires_at: int}
     */
    public function rememberAccessToken(User $user, string $accessToken, int $expiresIn): array
    {
        $token = ['access_token' => $accessToken, 'expires_at' => time() + $expiresIn];
        Cache::put($this->cacheKey($user), Crypt::encryptString(json_encode($token)), max(60, $expiresIn - 300));

        return $token;
    }

    public function forgetAccessToken(User $user): void
    {
        Cache::forget($this->cacheKey($user));
    }

    /**
     * Létezik-e még (és nincs kukában) a user mappája.
     */
    public function folderAlive(string $accessToken, ?string $folderId): bool
    {
        if (! $folderId) {
            return false;
        }

        $response = Http::withToken($accessToken)->get(self::FILES_URL.'/'.rawurlencode($folderId), ['fields' => 'id,trashed']);
        if ($response->notFound()) {
            return false;
        }
        $response->throw();

        return ! $response->json('trashed');
    }

    /**
     * A user élő SOSlive mappája: a tárolt ID, vagy egy (pl. a mobil app által) már létrehozott mappa, vagy új.
     */
    public function ensureFolder(User $user, string $accessToken): string
    {
        if ($this->folderAlive($accessToken, $user->drive_folder_id)) {
            return $user->drive_folder_id;
        }

        $folderId = $this->findFolder($accessToken) ?? $this->createFolder($accessToken);
        $user->forceFill(['drive_folder_id' => $folderId])->save();

        return $folderId;
    }

    public function findFolder(string $accessToken): ?string
    {
        $response = Http::withToken($accessToken)->get(self::FILES_URL, [
            'q' => "appProperties has { key='soslive' and value='root' } and mimeType='".self::FOLDER_MIME."' and trashed=false",
            'orderBy' => 'createdTime',
            'pageSize' => 1,
            'fields' => 'files(id)',
        ])->throw();

        return $response->json('files.0.id');
    }

    public function createFolder(string $accessToken): string
    {
        return Http::withToken($accessToken)
            ->withQueryParameters(['fields' => 'id'])
            ->post(self::FILES_URL, [
                'name' => config('soslive.folder_name'),
                'mimeType' => self::FOLDER_MIME,
                'appProperties' => ['soslive' => 'root'],
            ])
            ->throw()
            ->json('id');
    }

    private function cacheKey(User $user): string
    {
        return 'google-token:'.$user->getKey();
    }
}
