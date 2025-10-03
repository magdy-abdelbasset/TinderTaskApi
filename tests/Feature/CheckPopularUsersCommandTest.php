<?php

namespace Tests\Feature;

use App\Jobs\SendPopularUserNotification;
use App\Models\Like;
use App\Models\PopularUserNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CheckPopularUsersCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_finds_and_notifies_popular_users(): void
    {
        Queue::fake();

        // Create a user with 60 likes
        $popularUser = User::factory()->create(['name' => 'Popular User']);
        Like::factory()->count(60)->create(['to_user_id' => $popularUser->id]);

        // Create a user with only 30 likes
        $unpopularUser = User::factory()->create(['name' => 'Unpopular User']);
        Like::factory()->count(30)->create(['to_user_id' => $unpopularUser->id]);

        // Run the command
        $this->artisan('users:check-popular')
            ->expectsOutput('Checking for users with more than 50 likes...')
            ->expectsOutputToContain('Found popular user: Popular User')
            ->expectsOutput('Dispatched 1 popular user notifications.')
            ->assertExitCode(0);

        // Assert the job was dispatched
        Queue::assertPushed(SendPopularUserNotification::class, function ($job) use ($popularUser) {
            return $job->user->id === $popularUser->id && $job->likeCount === 60;
        });

        // Assert notification was recorded
        $this->assertDatabaseHas('popular_user_notifications', [
            'user_id' => $popularUser->id,
            'like_count' => 60,
            'threshold' => 50,
        ]);
    }

    public function test_command_respects_custom_threshold(): void
    {
        Queue::fake();

        // Create a user with 80 likes
        $user = User::factory()->create();
        Like::factory()->count(80)->create(['to_user_id' => $user->id]);

        // Run with custom threshold of 100
        $this->artisan('users:check-popular', ['--threshold' => 100])
            ->expectsOutput('Checking for users with more than 100 likes...')
            ->expectsOutput('No popular users found.')
            ->assertExitCode(0);

        Queue::assertNotPushed(SendPopularUserNotification::class);
    }

    public function test_command_prevents_duplicate_notifications(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        Like::factory()->count(60)->create(['to_user_id' => $user->id]);

        // Run command first time
        $this->artisan('users:check-popular')
            ->assertExitCode(0);

        Queue::assertPushed(SendPopularUserNotification::class, 1);

        // Run command second time
        $this->artisan('users:check-popular')
            ->expectsOutputToContain('already notified for threshold 50')
            ->expectsOutput('Dispatched 0 popular user notifications.')
            ->assertExitCode(0);

        // Should still only have been pushed once
        Queue::assertPushed(SendPopularUserNotification::class, 1);
    }

    public function test_command_force_option_sends_duplicate_notifications(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        Like::factory()->count(60)->create(['to_user_id' => $user->id]);

        // Run command first time
        $this->artisan('users:check-popular')->assertExitCode(0);

        // Run command second time with --force
        $this->artisan('users:check-popular', ['--force' => true])
            ->expectsOutput('Dispatched 1 popular user notifications.')
            ->assertExitCode(0);

        // Should have been pushed twice
        Queue::assertPushed(SendPopularUserNotification::class, 2);
    }

    public function test_command_handles_no_popular_users(): void
    {
        Queue::fake();

        // Create users with fewer than 50 likes
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Like::factory()->count(25)->create(['to_user_id' => $user1->id]);
        Like::factory()->count(35)->create(['to_user_id' => $user2->id]);

        $this->artisan('users:check-popular')
            ->expectsOutput('Checking for users with more than 50 likes...')
            ->expectsOutput('No popular users found.')
            ->assertExitCode(0);

        Queue::assertNothingPushed();
    }
}
