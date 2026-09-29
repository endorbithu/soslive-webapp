<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GoogleDrive;
use App\Support\UserConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->input('q'), fn ($q, $term) => $q->where(
                fn ($q) => $q->where('email', 'like', "%{$term}%")->orWhere('name', 'like', "%{$term}%")
            ))
            ->withCount('allowedEmails')
            ->orderByDesc('last_login_at')
            ->paginate(50)
            ->withQueryString();

        return view('admin.users.index', ['users' => $users]);
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', ['user' => $user, 'config' => UserConfig::toArray($user)]);
    }

    /**
     * Csak a max_events módosítható; a user config (értesítendők, hozzáférők) a mobil app felelőssége.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['max_events' => ['required', 'integer', 'min:1', 'max:100000']]);
        $user->update(['max_events' => (int) $data['max_events']]);

        return redirect()->route('admin.users.edit', $user)->with('status', 'Mentve.');
    }

    public function destroy(User $user, GoogleDrive $drive): RedirectResponse
    {
        // A user Drive-jában lévő fájlokhoz nem nyúlunk, csak a backend adatait töröljük.
        $drive->forgetAccessToken($user);
        DB::table('sessions')->where('user_id', $user->id)->delete();
        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'User törölve.');
    }
}
