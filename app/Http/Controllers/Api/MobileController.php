<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\GoogleReauthRequired;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GoogleDrive;
use App\Support\UserConfig;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Mobil API: belépés (session) és a user config olvasása / írása. A config csak innen módosítható,
 * így a mobil appnak nem kell pollingolnia a változásokért.
 */
class MobileController extends Controller
{
    /**
     * App indításkor: user létrehozása / frissítése, refresh token a serverAuthCode-ból, Drive mappa biztosítása.
     */
    public function session(Request $request, GoogleDrive $drive): JsonResponse
    {
        $request->validate(['server_auth_code' => ['nullable', 'string', 'max:1024']]);
        $google = $request->attributes->get('google');
        $user = User::findOrNewForGoogle($google['sub'], $google['email']);

        $token = null;
        if ($code = $request->input('server_auth_code')) {
            try {
                $token = $drive->exchangeServerAuthCode($code);
            } catch (RequestException $e) {
                report($e);

                return response()->json(['error' => 'invalid_server_auth_code'], 422);
            }
            if (! in_array(GoogleDrive::DRIVE_SCOPE, $token['scopes'], true)) {
                return response()->json(['error' => 'drive_scope_missing'], 422);
            }
        }

        // A web eseménylistájához a backendnek refresh token kell; ha nincs, a mobil kérjen új serverAuthCode-ot (consenttel).
        if (! ($token['refresh_token'] ?? null) && ! $user->google_refresh_token) {
            return response()->json(['error' => 'server_auth_code_required'], 409);
        }

        $user->syncGoogleProfile($google['sub'], $google['email'], $google['name'], $token['refresh_token'] ?? null);

        try {
            $accessToken = $token
                ? $drive->rememberAccessToken($user, $token['access_token'], $token['expires_in'])['access_token']
                : $drive->accessTokenFor($user)['access_token'];
            $drive->ensureFolder($user, $accessToken);
        } catch (GoogleReauthRequired) {
            return response()->json(['error' => 'server_auth_code_required'], 409);
        } catch (Throwable $e) {
            report($e); // a config ettől még visszamegy, drive_folder_id nélkül
        }

        return response()->json(UserConfig::toArray($user->refresh()));
    }

    public function show(Request $request): JsonResponse
    {
        return response()->json(UserConfig::toArray($this->registeredUser($request)));
    }

    public function update(Request $request): JsonResponse
    {
        $user = $this->registeredUser($request);
        UserConfig::save($user, UserConfig::validate(
            $request->only(['notification_emails', 'notification_phones', 'allowed_emails'])
        ));

        return response()->json(UserConfig::toArray($user->refresh()));
    }

    private function registeredUser(Request $request): User
    {
        $user = $request->user();
        abort_unless($user, response()->json(['error' => 'session_required'], 409));

        return $user;
    }
}
