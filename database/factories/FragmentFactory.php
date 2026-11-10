<?php

namespace Database\Factories;

use App\Models\Fragment;
use App\Models\Step;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fragment>
 */
class FragmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'step_id' => Step::factory(),
        ];
    }
}
