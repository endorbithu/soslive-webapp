<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Teszt belépés Google nélkül – csak nem production környezetben (APP_ENV != production).
 * Bármilyen email címmel belép (a user létrejön, ha még nincs). A Drive-os részekhez (eseménylista, beállítások)
 * a böngésző ettől még Google hozzáférést kér.
 */
class DevLoginController extends Controller
{
    public function create(): View
    {
        abort_if(app()->isProduction(), 404);

        return view('auth.dev-login');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if(app()->isProduction(), 404);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:128'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);
        $email = mb_strtolower($data['email']);

        $user = User::firstOrNew(['email' => $email]);
        $user->fill([
            'google_id' => $user->google_id ?: 'dev:'.$email,
            'name' => ($data['name'] ?? null) ?: ($user->name ?: 'Teszt '.strtok($email, '@')),
            'last_login_at' => now(),
        ])->save();

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
