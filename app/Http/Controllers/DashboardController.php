<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function home(Request $request)
    {
        return $request->user() ? redirect()->route('dashboard') : view('home');
    }

    public function index(Request $request): View
    {
        return view('dashboard', [
            'owners' => $this->ownersFor($request->user()),
        ]);
    }

    /**
     * A böngészőnek átadott tulaj-lista (csak azonosításhoz szükséges mezők).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function ownersFor(User $user): array
    {
        return $user->visibleOwners()->map(fn (User $owner) => [
            'id' => $owner->id,
            'name' => $owner->name ?: $owner->email,
            'email' => $owner->email,
            'is_me' => $owner->is($user),
        ])->values()->all();
    }
}
