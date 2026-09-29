<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GoogleDrive;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;
use Throwable;

class GoogleController extends Controller
{
    public function redirect(Request $request): SymfonyRedirect
    {
        $params = ['access_type' => 'offline', 'include_granted_scopes' => 'true'];
        if ($request->boolean('consent')) {
            // Csak így ad a Google újra refresh tokent egy korábban már engedélyezett usernek.
            $params['prompt'] = 'consent';
        }

        return Socialite::driver('google')
            ->scopes(config('soslive.scopes'))
            ->with($params)
            ->redirect();
    }

    public function callback(GoogleDrive $drive): RedirectResponse
    {
        try {
            $google = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('home', ['msg' => 'login_failed']);
        }

        if (! in_array(GoogleDrive::DRIVE_SCOPE, $google->approvedScopes ?? [], true)) {
            return redirect()->route('home', ['msg' => 'drive_scope']);
        }

        $user = User::findOrNewForGoogle($google->getId(), $google->getEmail());
        if (! $google->refreshToken && ! $user->google_refresh_token) {
            return redirect()->route('auth.google', ['consent' => 1]);
        }

        $user->syncGoogleProfile($google->getId(), $google->getEmail(), $google->getName(), $google->refreshToken);

        $drive->rememberAccessToken($user, $google->token, (int) ($google->expiresIn ?: 3600));

        $params = [];
        try {
            $drive->ensureFolder($user, $google->token);
        } catch (Throwable $e) {
            report($e);
            $params['msg'] = 'folder_error';
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        // A statikus oldalak nem látják a sessiont, ezért üzenet csak kódként, query paraméterben megy át.
        return redirect()->route('dashboard', $params);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
