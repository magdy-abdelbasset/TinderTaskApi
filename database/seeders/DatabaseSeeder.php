<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create users
        $users = User::factory(500)->create();

        // Create multiple images for each user
        foreach ($users as $user) {
            $imageCount = fake()->numberBetween(2, 12);
            for ($i = 0; $i < $imageCount; $i++) {
                $n = fake()->numberBetween(1, 25);
                \App\Models\UserImage::factory()->create([
                    'image_url' => asset("fake-images/users/{$n}.jpeg"),
                    'user_id' => $user->id,
                    'order' => $i,
                    'is_primary' => $i === 0, // First image is primary
                ]);
            }
        }

    }
}
