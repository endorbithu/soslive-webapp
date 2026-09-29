<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'google_id' => (string) fake()->unique()->numberBetween(10 ** 15, 10 ** 16),
            'google_refresh_token' => 'refresh-'.Str::random(20),
            'drive_folder_id' => 'folder-'.Str::random(10),
            'max_events' => 100,
            'remember_token' => Str::random(10),
        ];
    }
}
