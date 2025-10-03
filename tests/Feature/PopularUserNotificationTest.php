<?php

namespace Tests\Feature;

use App\Events\UserLiked;
use App\Jobs\SendPopularUserNotification;
use App\Mail\PopularUserAlert;
use App\Models\Like;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PopularUserNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_is_dispatched_when_user_gets_liked(): void
    {
        Event::fake();

        $fromUser = User::factory()->create();
        $toUser = User::factory()->create();

        $response = $this->postJson('/api/likes', [
            'from_user_id' => $fromUser->id,
            'to_user_id' => $toUser->id,
        ]);

        $response->assertStatus(201);

        Event::assertDispatched(UserLiked::class, function ($event) use ($toUser) {
            return $event->user->id === $toUser->id;
        });
    }

    public function test_job_is_dispatched_when_user_reaches_50_likes(): void
    {
        Queue::fake();
        // Don't fake events - let them run but capture the queue jobs

        $toUser = User::factory()->create();

        // Create 49 likes first
        Like::factory()->count(49)->create(['to_user_id' => $toUser->id]);

        // Create the 50th like through API
        $fromUser = User::factory()->create();
        $response = $this->postJson('/api/likes', [
            'from_user_id' => $fromUser->id,
            'to_user_id' => $toUser->id,
        ]);

        $response->assertStatus(201);

        Queue::assertPushed(SendPopularUserNotification::class, function ($job) use ($toUser) {
            return $job->user->id === $toUser->id && $job->likeCount === 50;
        });
    }

    public function test_job_sends_email_notification()
    {
        Mail::fake();
        
        $user = User::factory()->create([
            'name' => 'Popular User',
            'age' => 25,
            'location' => 'Test City'
        ]);
        
        $job = new SendPopularUserNotification($user, 55);
        $job->handle();
        
        Mail::assertSent(PopularUserAlert::class, function ($mail) use ($user) {
            return $mail->user->id === $user->id && 
                   $mail->likeCount === 55;
        });
    }

    public function test_job_is_dispatched_at_milestone_numbers(): void
    {
        Queue::fake();
        // Don't fake events - let them run but capture the queue jobs

        $toUser = User::factory()->create();

        // Create 99 likes first
        Like::factory()->count(99)->create(['to_user_id' => $toUser->id]);

        // Create the 100th like through API
        $fromUser = User::factory()->create();
        $response = $this->postJson('/api/likes', [
            'from_user_id' => $fromUser->id,
            'to_user_id' => $toUser->id,
        ]);

        $response->assertStatus(201);

        Queue::assertPushed(SendPopularUserNotification::class, function ($job) use ($toUser) {
            return $job->user->id === $toUser->id && $job->likeCount === 100;
        });
    }
}
