# Popular User Notifications - Cron Job Setup

This document explains how to set up automated notifications for popular users who receive more than 50 likes.

## Overview

The system includes:
- **Command**: `users:check-popular` - Checks for users with more than a specified number of likes
- **Job**: `SendPopularUserNotification` - Sends email notifications to admin
- **Model**: `PopularUserNotification` - Tracks sent notifications to prevent duplicates
- **Scheduler**: Configured to run hourly via Laravel's task scheduler

## Command Usage

### Basic Usage
```bash
php artisan users:check-popular
```
This checks for users with more than 50 likes (default threshold).

### Custom Threshold
```bash
php artisan users:check-popular --threshold=100
```
Check for users with more than 100 likes.

### Force Notifications
```bash
php artisan users:check-popular --force
```
Send notifications even if they were already sent for this threshold.

## Setting Up Cron Job

### Method 1: Laravel Scheduler (Recommended)

The command is already scheduled in `routes/console.php` to run hourly:

```php
Schedule::command('users:check-popular')->hourly();
```

Add this single cron entry to your server's crontab:

```bash
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

### Method 2: Direct Cron Entry

Alternatively, you can add the command directly to crontab:

```bash
# Run every hour at minute 0
0 * * * * cd /path/to/your/project && php artisan users:check-popular >> /dev/null 2>&1

# Run every 30 minutes
*/30 * * * * cd /path/to/your/project && php artisan users:check-popular >> /dev/null 2>&1

# Run daily at 2 AM
0 2 * * * cd /path/to/your/project && php artisan users:check-popular >> /dev/null 2>&1
```

### Method 3: Different Thresholds

You can set up multiple cron jobs for different thresholds:

```bash
# Check for 50+ likes every hour
0 * * * * cd /path/to/your/project && php artisan users:check-popular --threshold=50

# Check for 100+ likes every 6 hours
0 */6 * * * cd /path/to/your/project && php artisan users:check-popular --threshold=100

# Check for 500+ likes daily
0 9 * * * cd /path/to/your/project && php artisan users:check-popular --threshold=500
```

## How It Works

1. **Detection**: Command queries users with `likes_received_count >= threshold`
2. **Deduplication**: Checks `popular_user_notifications` table to avoid duplicate emails
3. **Notification**: Dispatches `SendPopularUserNotification` job for each new popular user
4. **Email**: Job sends HTML email to admin using `PopularUserAlert` mailable
5. **Tracking**: Records notification in database with user_id, threshold, and timestamp

## Queue Processing

Since notifications are sent via queued jobs, ensure your queue worker is running:

```bash
# Start queue worker (for production)
php artisan queue:work --daemon

# For development/testing
php artisan queue:work
```

## Testing

Run the comprehensive test suite:

```bash
php artisan test --filter=CheckPopularUsersCommandTest
```

Test manually with different thresholds:

```bash
# See all users regardless of likes
php artisan users:check-popular --threshold=0

# Test with force flag
php artisan users:check-popular --force

# Check command help
php artisan users:check-popular --help
```

## Monitoring

### View Sent Notifications
```sql
SELECT 
    pun.*,
    u.name as user_name,
    u.email as user_email
FROM popular_user_notifications pun
JOIN users u ON pun.user_id = u.id
ORDER BY pun.created_at DESC;
```

### Check Popular Users
```sql
SELECT 
    u.id,
    u.name,
    COUNT(l.id) as total_likes
FROM users u
LEFT JOIN likes l ON u.id = l.to_user_id
GROUP BY u.id, u.name
HAVING total_likes >= 50
ORDER BY total_likes DESC;
```

## Configuration

### Email Settings

Configure email settings in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="Your App Name"
```

### Queue Settings

Configure queue in `.env`:

```env
QUEUE_CONNECTION=database
# or
QUEUE_CONNECTION=redis
```

## Troubleshooting

### Command Not Found
If `users:check-popular` command is not found, clear the command cache:
```bash
php artisan config:clear
php artisan cache:clear
```

### No Emails Sent
1. Check queue is processing: `php artisan queue:work`
2. Verify email configuration in `.env`
3. Check `failed_jobs` table for failed queue jobs
4. Test email manually: `php artisan tinker` then `Mail::raw('test', function($m) { $m->to('test@example.com')->subject('test'); });`

### Duplicate Notifications
The system prevents duplicates automatically, but if needed:
```bash
# Clear notification history
php artisan tinker --execute="App\Models\PopularUserNotification::truncate();"

# Or delete for specific threshold
php artisan tinker --execute="App\Models\PopularUserNotification::where('threshold', 50)->delete();"
```

## Performance Considerations

- The command uses efficient queries with `withCount()` and `having()`
- Notifications are tracked to prevent unnecessary processing
- Jobs are queued to avoid blocking the command execution
- Consider adding database indexes if you have many users:

```sql
CREATE INDEX idx_likes_to_user_id ON likes(to_user_id);
CREATE INDEX idx_popular_notifications_user_threshold ON popular_user_notifications(user_id, threshold);
```
