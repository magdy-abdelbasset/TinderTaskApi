<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Tinder Task API",
 *     version="1.0.0",
 *     description="API for a Tinder-like application with users and likes functionality",
 *     @OA\Contact(
 *         email="contact@tindertask.com"
 *     )
 * )
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Local development server"
 * )
 * @OA\Tag(
 *     name="Users",
 *     description="User management endpoints"
 * )
 * @OA\Tag(
 *     name="Likes", 
 *     description="User likes management endpoints"
 * )
 */
abstract class Controller
{
    //
}
