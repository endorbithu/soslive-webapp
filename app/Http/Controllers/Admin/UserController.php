<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Admin: a Google SSO-val belépett userek listája és törlése. A userek adatai (események, config) a saját
 * Google Drive-jukon vannak, azokat a backend nem látja.
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->input('q'), fn ($q, $term) => $q->where(
                fn ($q) => $q->where('email', 'like', "%{$term}%")->orWhere('name', 'like', "%{$term}%")
            ))
            ->orderByDesc('last_login_at')
            ->paginate(50)
            ->withQueryString();

        return view('admin.users.index', ['users' => $users]);
    }

    public function destroy(User $user): RedirectResponse
    {
        // A user Drive-jában lévő fájlokhoz nem nyúlunk, csak a backend adatait töröljük.
        DB::table('sessions')->where('user_id', $user->id)->delete();
        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'User törölve.');
    }
}
