<?php

namespace Database\Factories;

use App\Models\Step;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Step>
 */
class StepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $id = fake()->unique()->numberBetween(100, 999);

        return [
            'name' => fake()->sentence(3),
            'lat' => fake()->latitude(50.40, 50.46),
            'lng' => fake()->longitude(2.78, 2.85),
            'riddle_text' => fake()->paragraph(),
            'qr_map_code' => "MAP_LOC_{$id}",
            'qr_mj_code' => "MJ_STEP_{$id}",
            'code_part' => (string) fake()->randomDigit(),
        ];
    }
}
