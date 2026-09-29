<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Google SSO-val belépett user. A backend csak az azonosításhoz szükséges adatokat tárolja; az események és a
 * config (értesítendők, max_events) a user saját Google Drive-ján vannak.
 */
#[Fillable(['email', 'google_id', 'name', 'last_login_at'])]
#[Hidden(['remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
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
            ?? new self;
    }

    /**
     * Belépéskor frissíti a Google profil adatait és menti a usert.
     */
    public function syncGoogleProfile(string $googleId, string $email, ?string $name): void
    {
        $this->fill([
            'google_id' => $googleId,
            'email' => mb_strtolower($email),
            'name' => $name ?: $this->name,
            'last_login_at' => now(),
        ])->save();
    }
}
