<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_paginated_users(): void
    {
        // Create some test users
        User::factory()->count(25)->create();

        // Make API request
        $response = $this->getJson('/api/users');

        // Assert response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'age',
                        'location',
                        'images',
                        'primary_image',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'links',
                    'path',
                    'per_page',
                    'to',
                    'total',
                ],
            ]);

        // Assert pagination details
        $response->assertJson([
            'meta' => [
                'current_page' => 1,
                'per_page' => 15,
                'total' => 25,
            ],
        ]);

        // Assert we get 15 users on the first page
        $this->assertCount(15, $response->json('data'));
    }

    public function test_can_navigate_to_second_page(): void
    {
        // Create 20 test users
        User::factory()->count(20)->create();

        // Make API request to second page
        $response = $this->getJson('/api/users?page=2');

        // Assert response
        $response->assertStatus(200);

        // Assert pagination details
        $response->assertJson([
            'meta' => [
                'current_page' => 2,
                'per_page' => 15,
                'total' => 20,
            ],
        ]);

        // Assert we get 5 users on the second page (20 total - 15 on first page)
        $this->assertCount(5, $response->json('data'));
    }
}
