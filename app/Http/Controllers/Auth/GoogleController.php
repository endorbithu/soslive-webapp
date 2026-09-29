<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;
use Throwable;

class GoogleController extends Controller
{
    private const DRIVE_SCOPE = 'https://www.googleapis.com/auth/drive.file';

    public function redirect(): SymfonyRedirect
    {
        // A drive.file engedélyt már itt megkérjük: így a böngésző később (Google Identity Services) consent képernyő
        // nélkül kaphat tokent a saját Drive mappájához. Offline hozzáférés / refresh token nem kell, a backend nem hív Google API-t.
        return Socialite::driver('google')
            ->scopes(config('soslive.scopes'))
            ->with(['include_granted_scopes' => 'true'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $google = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('home', ['msg' => 'login_failed']);
        }

        if (! in_array(self::DRIVE_SCOPE, $google->approvedScopes ?? [], true)) {
            return redirect()->route('home', ['msg' => 'drive_scope']);
        }

        $user = User::findOrNewForGoogle($google->getId(), $google->getEmail());
        $user->syncGoogleProfile($google->getId(), $google->getEmail(), $google->getName());

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
