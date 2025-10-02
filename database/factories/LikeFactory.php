<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Like>
 */
class LikeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'from_user_id' => \App\Models\User::factory(),
            'to_user_id' => \App\Models\User::factory(),
        ];
    }

    /**
     * Create a like with nullable from_user_id (anonymous like).
     */
    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'from_user_id' => null,
        ]);
    }
}
