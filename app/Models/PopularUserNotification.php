<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PopularUserNotification extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'like_count',
        'threshold',
        'notified_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'notified_at' => 'datetime',
    ];

    /**
     * Get the user that was notified about.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
