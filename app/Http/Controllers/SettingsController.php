<?php

namespace App\Http\Controllers;

use App\Exceptions\GoogleReauthRequired;
use App\Models\User;
use App\Services\GoogleDrive;
use App\Support\ListField;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;

class SettingsController extends Controller
{
    public const PHONE_PATTERN = '/^\+?[0-9][0-9 ()\-]{5,19}$/';

    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('settings', [
            'user' => $user,
            'allowedEmails' => $user->allowedEmails()->orderBy('email')->pluck('email')->implode("\n"),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = self::validated($request);

        DB::transaction(function () use ($user, $data) {
            $user->update([
                'notification_emails' => ListField::join($data['notification_emails']),
                'notification_phones' => ListField::join($data['notification_phones']),
            ]);
            // A notification címzettek automatikusan láthatják az eseményeket.
            self::syncAllowedEmails($user, array_merge($data['allowed_emails'], $data['notification_emails']));
        });

        return back()->with('status', 'Beállítások elmentve.');
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

    /**
     * Közös validáció (user beállítások és admin szerkesztés).
     *
     * @return array{notification_emails: list<string>, notification_phones: list<string>, allowed_emails: list<string>}
     */
    public static function validated(Request $request): array
    {
        $request->validate([
            'notification_emails' => ['nullable', 'string', 'max:255'],
            'notification_phones' => ['nullable', 'string', 'max:255'],
            'allowed_emails' => ['nullable', 'string', 'max:10000'],
        ]);

        $data = [
            'notification_emails' => ListField::emails($request->input('notification_emails')),
            'notification_phones' => ListField::phones($request->input('notification_phones')),
            'allowed_emails' => ListField::emails($request->input('allowed_emails')),
        ];

        Validator::make($data, [
            'notification_emails.*' => ['email', 'max:128'],
            'notification_phones.*' => ['regex:'.self::PHONE_PATTERN],
            'allowed_emails.*' => ['email', 'max:128'],
        ], [
            'notification_emails.*.email' => 'Érvénytelen email cím: :input',
            'allowed_emails.*.email' => 'Érvénytelen email cím: :input',
            'notification_phones.*.regex' => 'Érvénytelen telefonszám: :input',
        ])->validate();

        // Az újra összefűzött szöveg se lépje túl a 255 karaktert.
        Validator::make([
            'notification_emails' => (string) ListField::join($data['notification_emails']),
            'notification_phones' => (string) ListField::join($data['notification_phones']),
        ], [
            'notification_emails' => ['max:255'],
            'notification_phones' => ['max:255'],
        ])->validate();

        return $data;
    }

    /**
     * @param  list<string>  $emails
     */
    public static function syncAllowedEmails(User $user, array $emails): void
    {
        $emails = array_values(array_diff(array_unique($emails), [$user->email]));

        $user->allowedEmails()->whereNotIn('email', $emails)->delete();
        $existing = $user->allowedEmails()->pluck('email')->all();
        foreach (array_diff($emails, $existing) as $email) {
            $user->allowedEmails()->create(['email' => $email]);
        }
    }
}
