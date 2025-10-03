<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeRequestValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_like_validates_required_fields(): void
    {
        $response = $this->postJson('/api/likes', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['to_user_id'])
            ->assertJsonPath('errors.to_user_id.0', 'The user to receive the like is required.');
    }

    public function test_store_like_validates_user_existence(): void
    {
        $response = $this->postJson('/api/likes', [
            'from_user_id' => 9999,
            'to_user_id' => 9998,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['from_user_id', 'to_user_id'])
            ->assertJsonPath('errors.from_user_id.0', 'The selected user who is giving the like does not exist.')
            ->assertJsonPath('errors.to_user_id.0', 'The selected user to receive the like does not exist.');
    }

    public function test_store_like_allows_null_from_user_id(): void
    {
        $toUser = User::factory()->create();

        $response = $this->postJson('/api/likes', [
            'from_user_id' => null,
            'to_user_id' => $toUser->id,
        ]);

        $response->assertStatus(201);
    }

    public function test_remove_like_validates_required_fields(): void
    {
        $response = $this->deleteJson('/api/likes/remove', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['to_user_id'])
            ->assertJsonPath('errors.to_user_id.0', 'The user who received the like is required.');
    }

    public function test_remove_like_validates_user_existence(): void
    {
        $response = $this->deleteJson('/api/likes/remove', [
            'from_user_id' => 9999,
            'to_user_id' => 9998,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['from_user_id', 'to_user_id'])
            ->assertJsonPath('errors.from_user_id.0', 'The selected user who gave the like does not exist.')
            ->assertJsonPath('errors.to_user_id.0', 'The selected user who received the like does not exist.');
    }

    public function test_remove_like_allows_null_from_user_id(): void
    {
        $toUser = User::factory()->create();

        // First create an anonymous like
        $this->postJson('/api/likes', [
            'from_user_id' => null,
            'to_user_id' => $toUser->id,
        ]);

        // Then remove it
        $response = $this->deleteJson('/api/likes/remove', [
            'from_user_id' => null,
            'to_user_id' => $toUser->id,
        ]);

        $response->assertStatus(200);
    }
}
