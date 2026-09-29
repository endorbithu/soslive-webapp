<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * A user config (értesítendő emailek / telefonszámok, hozzáférők) olvasása, validálása és mentése.
 * A config csak a mobil appból módosítható (PUT /api/config); a web és az admin csak megjeleníti.
 */
class UserConfig
{
    public const PHONE_PATTERN = '/^\+?[0-9][0-9 ()\-]{5,19}$/';

    /**
     * @return array<string, mixed>
     */
    public static function toArray(User $user): array
    {
        return [
            'email' => $user->email,
            'name' => $user->name,
            'drive_folder_id' => $user->drive_folder_id,
            'max_events' => $user->max_events,
            'notification_emails' => ListField::emails($user->notification_emails),
            'notification_phones' => ListField::phones($user->notification_phones),
            'allowed_emails' => $user->allowedEmails()->orderBy('email')->pluck('email')->all(),
        ];
    }

    /**
     * Listaként (tömb) vagy elválasztott szövegként is elfogadja a mezőket.
     *
     * @return array{notification_emails: list<string>, notification_phones: list<string>, allowed_emails: list<string>}
     *
     * @throws ValidationException
     */
    public static function validate(array $input): array
    {
        $shape = function (string $attribute, mixed $value, \Closure $fail) {
            $ok = is_string($value) && mb_strlen($value) <= 10000
                || is_array($value) && array_is_list($value) && count($value) <= 200
                    && count(array_filter($value, 'is_string')) === count($value);
            if (! $ok) {
                $fail("A(z) {$attribute} mező szöveg vagy szövegek listája legyen.");
            }
        };
        Validator::make($input, [
            'notification_emails' => ['nullable', $shape],
            'notification_phones' => ['nullable', $shape],
            'allowed_emails' => ['nullable', $shape],
        ])->validate();

        $data = [
            'notification_emails' => ListField::emails(self::text($input['notification_emails'] ?? null)),
            'notification_phones' => ListField::phones(self::text($input['notification_phones'] ?? null, "\n")),
            'allowed_emails' => ListField::emails(self::text($input['allowed_emails'] ?? null)),
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

        // Az adatbázisban vesszővel összefűzve tároljuk, max 255 karakter.
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
     * @param  array{notification_emails: list<string>, notification_phones: list<string>, allowed_emails: list<string>}  $data
     */
    public static function save(User $user, array $data): void
    {
        DB::transaction(function () use ($user, $data) {
            $user->update([
                'notification_emails' => ListField::join($data['notification_emails']),
                'notification_phones' => ListField::join($data['notification_phones']),
            ]);

            // A notification címzettek automatikusan láthatják az eseményeket.
            $emails = array_unique(array_merge($data['allowed_emails'], $data['notification_emails']));
            $emails = array_values(array_diff($emails, [$user->email]));

            $user->allowedEmails()->whereNotIn('email', $emails)->delete();
            $existing = $user->allowedEmails()->pluck('email')->all();
            foreach (array_diff($emails, $existing) as $email) {
                $user->allowedEmails()->create(['email' => $email]);
            }
        });
    }

    private static function text(mixed $value, string $glue = ','): ?string
    {
        return is_array($value) ? implode($glue, $value) : $value;
    }
}
