<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SettingsController;
use App\Models\User;
use App\Services\GoogleDrive;
use App\Support\ListField;
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
        return view('admin.users.edit', [
            'user' => $user,
            'allowedEmails' => $user->allowedEmails()->orderBy('email')->pluck('email')->implode("\n"),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate(['max_events' => ['required', 'integer', 'min:1', 'max:100000']]);
        $data = SettingsController::validated($request);

        DB::transaction(function () use ($request, $user, $data) {
            $user->update([
                'max_events' => (int) $request->input('max_events'),
                'notification_emails' => ListField::join($data['notification_emails']),
                'notification_phones' => ListField::join($data['notification_phones']),
            ]);
            SettingsController::syncAllowedEmails($user, array_merge($data['allowed_emails'], $data['notification_emails']));
        });

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
