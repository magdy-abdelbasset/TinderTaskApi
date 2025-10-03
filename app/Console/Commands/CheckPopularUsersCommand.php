<?php

namespace App\Console\Commands;

use App\Jobs\SendPopularUserNotification;
use App\Models\PopularUserNotification;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckPopularUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:check-popular {--threshold=50 : Minimum number of likes to be considered popular} {--force : Force send notifications even if already sent}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for users who have received more than the threshold number of likes and send admin notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $threshold = (int) $this->option('threshold');
        $force = $this->option('force');

        $this->info("Checking for users with more than {$threshold} likes...");

        // Get users with more than the threshold number of likes
        $popularUsers = User::withCount('likesReceived')
            ->having('likes_received_count', '>=', $threshold)
            ->get();

        if ($popularUsers->isEmpty()) {
            $this->info('No popular users found.');
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($popularUsers as $user) {
            $likeCount = $user->likes_received_count;
            
            // Check if we've already sent notification for this threshold
            if (!$force) {
                $existingNotification = PopularUserNotification::where('user_id', $user->id)
                    ->where('threshold', $threshold)
                    ->first();

                if ($existingNotification) {
                    $this->line("Skipping {$user->name} (ID: {$user->id}) - already notified for threshold {$threshold}");
                    continue;
                }
            }
            
            $this->line("Found popular user: {$user->name} (ID: {$user->id}) with {$likeCount} likes");
            
            // Dispatch the notification job
            SendPopularUserNotification::dispatch($user, $likeCount);
            
            // Record that we've sent this notification
            PopularUserNotification::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'threshold' => $threshold,
                ],
                [
                    'like_count' => $likeCount,
                    'notified_at' => now(),
                ]
            );
            
            $count++;
        }

        $this->info("Dispatched {$count} popular user notifications.");
        
        return Command::SUCCESS;
    }
}
