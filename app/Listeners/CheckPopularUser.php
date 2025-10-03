<?php

namespace App\Listeners;

use App\Events\UserLiked;
use App\Jobs\SendPopularUserNotification;

class CheckPopularUser
{

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserLiked $event): void
    {
        $user = $event->user;
        $likeCount = $user->likesReceived()->count();

        // Check if user just hit exactly 50 likes to avoid sending multiple emails
        if ($likeCount === 50) {
            SendPopularUserNotification::dispatch($user, $likeCount);
        }
        
        // Also send notification at milestones (100, 150, 200, etc.)
        if ($likeCount > 50 && $likeCount % 50 === 0) {
            SendPopularUserNotification::dispatch($user, $likeCount);
        }
    }
}
