<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;

#[Fillable([
    'email', 'google_id', 'name', 'google_refresh_token', 'drive_folder_id',
    'notification_emails', 'notification_phones', 'max_events', 'last_login_at',
])]
#[Hidden(['google_refresh_token', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'google_refresh_token' => 'encrypted',
            'max_events' => 'integer',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * A Google fiókhoz tartozó user (google_id, majd email alapján), vagy egy új, még nem mentett user.
     */
    public static function findOrNewForGoogle(string $googleId, string $email): self
    {
        return self::where('google_id', $googleId)->first()
            ?? self::where('email', mb_strtolower($email))->first()
            ?? new self(['max_events' => config('soslive.default_max_events')]);
    }

    /**
     * Belépéskor (web vagy mobil) frissíti a Google profil adatait és menti a usert.
     */
    public function syncGoogleProfile(string $googleId, string $email, ?string $name, ?string $refreshToken): void
    {
        $this->fill([
            'google_id' => $googleId,
            'email' => mb_strtolower($email),
            'name' => $name ?: $this->name,
            'google_refresh_token' => $refreshToken ?: $this->google_refresh_token,
            'last_login_at' => now(),
        ])->save();
    }

    /** @return HasMany<UserAllowedEmail, $this> */
    public function allowedEmails(): HasMany
    {
        return $this->hasMany(UserAllowedEmail::class);
    }

    /**
     * Láthatja-e $viewer ennek a usernek az eseményeit.
     */
    public function isVisibleTo(User $viewer): bool
    {
        return $viewer->is($this)
            || $this->allowedEmails()->where('email', mb_strtolower($viewer->email))->exists();
    }

    /**
     * Azok a userek (saját magát is beleértve), akiknek az eseményeit ez a user láthatja.
     *
     * @return Collection<int, User>
     */
    public function visibleOwners()
    {
        $others = User::query()
            ->whereKeyNot($this->getKey())
            ->whereHas('allowedEmails', fn ($q) => $q->where('email', mb_strtolower($this->email)))
            ->orderBy('name')
            ->get();

        return collect([$this])->concat($others);
    }
}
