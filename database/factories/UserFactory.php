<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'team_name' => fake()->words(2, true),
            'avatar' => 'gali',
            'reward_picked' => false,
            'reward_token' => null,
            'current_step' => 1,
            'phase' => 'GUESSING_LOCATION',
        ];
    }

    /**
     * Indicate that the user is in walking phase.
     */
    public function walking(): static
    {
        return $this->state(fn (array $attributes) => [
            'phase' => 'WALKING_TO_TARGET',
        ]);
    }

    /**
     * Indicate that the user has completed all steps.
     */
    public function finished(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_step' => 7,
            'phase' => 'GUESSING_LOCATION',
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
