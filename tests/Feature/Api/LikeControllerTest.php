<?php

namespace Tests\Feature\Api;

use App\Models\Like;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_like_with_from_user(): void
    {
        $fromUser = User::factory()->create();
        $toUser = User::factory()->create();

        $response = $this->postJson('/api/likes', [
            'from_user_id' => $fromUser->id,
            'to_user_id' => $toUser->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'from_user_id',
                'to_user_id',
                'from_user' => ['id', 'name', 'image'],
                'to_user' => ['id', 'name', 'image'],
                'created_at',
                'updated_at',
            ]);

        $this->assertDatabaseHas('likes', [
            'from_user_id' => $fromUser->id,
            'to_user_id' => $toUser->id,
        ]);
    }

    public function test_can_create_anonymous_like(): void
    {
        $toUser = User::factory()->create();

        $response = $this->postJson('/api/likes', [
            'from_user_id' => null,
            'to_user_id' => $toUser->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'from_user_id' => null,
                'to_user_id' => $toUser->id,
            ]);

        $this->assertDatabaseHas('likes', [
            'from_user_id' => null,
            'to_user_id' => $toUser->id,
        ]);
    }

    public function test_can_get_likes_for_user(): void
    {
        $fromUser = User::factory()->create();
        $toUser = User::factory()->create();

        // Create regular like
        Like::create([
            'from_user_id' => $fromUser->id,
            'to_user_id' => $toUser->id,
        ]);

        // Create anonymous like
        Like::create([
            'from_user_id' => null,
            'to_user_id' => $toUser->id,
        ]);

        $response = $this->getJson("/api/likes?user_id={$toUser->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'from_user_id',
                        'to_user_id',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta',
            ]);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_prevents_duplicate_likes(): void
    {
        $fromUser = User::factory()->create();
        $toUser = User::factory()->create();

        // Create first like
        $this->postJson('/api/likes', [
            'from_user_id' => $fromUser->id,
            'to_user_id' => $toUser->id,
        ])->assertStatus(201);

        // Try to create duplicate like
        $response = $this->postJson('/api/likes', [
            'from_user_id' => $fromUser->id,
            'to_user_id' => $toUser->id,
        ]);

        $response->assertStatus(409)
            ->assertJson(['error' => 'Like already exists']);
    }

    public function test_can_remove_like_with_from_user(): void
    {
        $fromUser = User::factory()->create();
        $toUser = User::factory()->create();

        // Create a like first
        Like::create([
            'from_user_id' => $fromUser->id,
            'to_user_id' => $toUser->id,
        ]);

        $response = $this->deleteJson('/api/likes/remove', [
            'from_user_id' => $fromUser->id,
            'to_user_id' => $toUser->id,
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Like removed successfully']);

        $this->assertDatabaseMissing('likes', [
            'from_user_id' => $fromUser->id,
            'to_user_id' => $toUser->id,
        ]);
    }

    public function test_can_remove_anonymous_like(): void
    {
        $toUser = User::factory()->create();

        // Create an anonymous like first
        Like::create([
            'from_user_id' => null,
            'to_user_id' => $toUser->id,
        ]);

        $response = $this->deleteJson('/api/likes/remove', [
            'from_user_id' => null,
            'to_user_id' => $toUser->id,
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Like removed successfully']);

        $this->assertDatabaseMissing('likes', [
            'from_user_id' => null,
            'to_user_id' => $toUser->id,
        ]);
    }

    public function test_remove_like_returns_404_when_like_not_found(): void
    {
        $fromUser = User::factory()->create();
        $toUser = User::factory()->create();

        $response = $this->deleteJson('/api/likes/remove', [
            'from_user_id' => $fromUser->id,
            'to_user_id' => $toUser->id,
        ]);

        $response->assertStatus(404)
            ->assertJson(['error' => 'Like not found']);
    }

    public function test_remove_like_validates_user_existence(): void
    {
        $response = $this->deleteJson('/api/likes/remove', [
            'from_user_id' => 9999,
            'to_user_id' => 9998,
        ]);

        $response->assertStatus(422);
    }
}
