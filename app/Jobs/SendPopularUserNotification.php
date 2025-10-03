<?php

namespace App\Jobs;

use App\Mail\PopularUserAlert;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendPopularUserNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public int $likeCount
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $adminEmail = 'admin@tindertask.com';
        
        Mail::to($adminEmail)->send(new PopularUserAlert($this->user, $this->likeCount));
    }
}
