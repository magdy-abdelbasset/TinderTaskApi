<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'age',
        'location',
    ];

    /**
     * Get the likes given by this user.
     */
    public function likesGiven(): HasMany
    {
        return $this->hasMany(Like::class, 'from_user_id');
    }

    /**
     * Get the likes received by this user.
     */
    public function likesReceived(): HasMany
    {
        return $this->hasMany(Like::class, 'to_user_id');
    }

    /**
     * Get all images for this user.
     */
    public function images(): HasMany
    {
        return $this->hasMany(UserImage::class)->orderBy('order');
    }

    /**
     * Get the primary image for this user.
     */
    public function primaryImage(): HasMany
    {
        return $this->hasMany(UserImage::class)->where('is_primary', true);
    }
}
