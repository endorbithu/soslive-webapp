<?php

namespace App\Http\Controllers;

use App\Exceptions\GoogleReauthRequired;
use App\Services\GoogleDrive;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * A Beállítások oldal (statikus, routes/static.php) egyetlen írási művelete: a Drive mappa ellenőrzése / újralétrehozása.
 */
class SettingsController extends Controller
{
    public function folder(Request $request, GoogleDrive $drive): RedirectResponse
    {
        $user = $request->user();

        try {
            $token = $drive->accessTokenFor($user);
            $drive->ensureFolder($user, $token['access_token']);
        } catch (GoogleReauthRequired) {
            return redirect()->route('auth.google', ['consent' => 1]);
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('settings', ['msg' => 'folder_error']);
        }

        return redirect()->route('settings', ['msg' => 'folder_ok']);
    }
}
