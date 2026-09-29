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
    private const DRIVE_SCOPE = 'https://www.googleapis.com/auth/drive.file';

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

            return redirect()->route('home')->with('error', 'A Google bejelentkezés nem sikerült.');
        }

        if (! in_array(self::DRIVE_SCOPE, $google->approvedScopes ?? [], true)) {
            return redirect()->route('home')
                ->with('error', 'A működéshez engedélyezni kell a Google Drive hozzáférést (csak az app által létrehozott fájlok).');
        }

        $user = User::where('google_id', $google->getId())->first()
            ?? User::where('email', mb_strtolower($google->getEmail()))->first()
            ?? new User(['max_events' => config('soslive.default_max_events')]);

        $refreshToken = $google->refreshToken ?: $user->google_refresh_token;
        if (! $refreshToken) {
            return redirect()->route('auth.google', ['consent' => 1]);
        }

        $user->fill([
            'google_id' => $google->getId(),
            'email' => mb_strtolower($google->getEmail()),
            'name' => $google->getName(),
            'google_refresh_token' => $refreshToken,
            'last_login_at' => now(),
        ])->save();

        $drive->rememberAccessToken($user, $google->token, (int) ($google->expiresIn ?: 3600));

        try {
            $drive->ensureFolder($user, $google->token);
        } catch (Throwable $e) {
            report($e);
            session()->flash('error', 'A SOSlive mappát nem sikerült elérni/létrehozni a Drive-on. Próbáld újra a Beállításokban.');
        }

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
