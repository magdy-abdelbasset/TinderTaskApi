<?php

use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::apiResource('users', UserController::class);
Route::delete('likes/remove', [LikeController::class, 'removeLike']);
Route::apiResource('likes', LikeController::class);
