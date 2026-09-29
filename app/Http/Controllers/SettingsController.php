<?php

namespace App\Http\Controllers;

use App\Exceptions\GoogleReauthRequired;
use App\Services\GoogleDrive;
use App\Support\UserConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

/**
 * Beállítások oldal: a user config csak olvasható (a mobil appban módosítható), a Drive mappa itt ellenőrizhető.
 */
class SettingsController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        return view('settings', ['user' => $user, 'config' => UserConfig::toArray($user)]);
    }

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

            return back()->with('error', 'A mappát nem sikerült létrehozni.');
        }

        return back()->with('status', 'A SOSlive mappa rendben van.');
    }
}
