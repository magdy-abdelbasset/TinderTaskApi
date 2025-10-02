<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'age' => fake()->numberBetween(18, 65),
            'location' => fake()->city() . ', ' . fake()->stateAbbr(),
            'image' => 'https://picsum.photos/400/600?random=' . fake()->numberBetween(1, 1000),
        ];
    }
}
