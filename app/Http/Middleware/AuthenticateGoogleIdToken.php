<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\GoogleIdTokenVerifier;
use Closure;
use DomainException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

/**
 * Mobil API hitelesítés: `Authorization: Bearer <Google ID token>` minden kérésben (állapotmentes, nincs saját token).
 * Az ellenőrzött Google azonosítók a `google` request attribútumba kerülnek; ha már van ilyen user, ő lesz a $request->user().
 */
class AuthenticateGoogleIdToken
{
    public function __construct(private GoogleIdTokenVerifier $verifier) {}

    public function handle(Request $request, Closure $next): Response
    {
        $idToken = $request->bearerToken();
        if (! $idToken) {
            return response()->json(['error' => 'missing_token'], 401);
        }

        try {
            $google = $this->verifier->verify($idToken);
        } catch (UnexpectedValueException|DomainException) { // érvénytelen / lejárt / hibás formátumú token
            return response()->json(['error' => 'invalid_token'], 401);
        }

        $request->attributes->set('google', $google);
        $user = User::where('google_id', $google['sub'])->first();
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
