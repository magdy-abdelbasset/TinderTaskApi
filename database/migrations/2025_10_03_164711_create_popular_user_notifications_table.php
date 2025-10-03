<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('popular_user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('like_count');
            $table->integer('threshold');
            $table->timestamp('notified_at');
            $table->timestamps();

            // Prevent duplicate notifications for the same threshold
            $table->unique(['user_id', 'threshold']);
            
            // Add indexes for better performance
            $table->index('user_id');
            $table->index('threshold');
            $table->index('notified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('popular_user_notifications');
    }
};
