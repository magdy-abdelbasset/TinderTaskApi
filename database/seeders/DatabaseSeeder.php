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
        $users = User::factory(10)->create();

        // Create multiple images for each user
        foreach ($users as $user) {
            // Create 2-4 images per user
            $imageCount = fake()->numberBetween(2, 4);
            
            for ($i = 0; $i < $imageCount; $i++) {
                \App\Models\UserImage::factory()->create([
                    'user_id' => $user->id,
                    'order' => $i,
                    'is_primary' => $i === 0, // First image is primary
                ]);
            }
        }

        // Create test user with custom images
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'age' => 25,
            'location' => 'Cairo, Egypt',
            'image' => 'https://picsum.photos/400/600?random=1001',
        ]);

        // Add multiple images for test user
        \App\Models\UserImage::factory()->create([
            'user_id' => $testUser->id,
            'image_url' => 'https://picsum.photos/400/600?random=1001',
            'order' => 0,
            'is_primary' => true,
        ]);

        \App\Models\UserImage::factory()->create([
            'user_id' => $testUser->id,
            'image_url' => 'https://picsum.photos/400/600?random=1002',
            'order' => 1,
            'is_primary' => false,
        ]);

        \App\Models\UserImage::factory()->create([
            'user_id' => $testUser->id,
            'image_url' => 'https://picsum.photos/400/600?random=1003',
            'order' => 2,
            'is_primary' => false,
        ]);
    }
}
