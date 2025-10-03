<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Get paginated list of users",
     *     description="Returns a paginated list of all users with their profile information and images",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="age", type="integer", example=25),
     *                     @OA\Property(property="location", type="string", example="New York, NY"),
     *                     @OA\Property(property="images", type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="image_url", type="string", example="https://picsum.photos/400/600?random=123"),
     *                             @OA\Property(property="order", type="integer", example=0),
     *                             @OA\Property(property="is_primary", type="boolean", example=true)
     *                         )
     *                     ),
     *                     @OA\Property(property="primary_image", type="string", example="https://picsum.photos/400/600?random=123"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(property="links", type="object",
     *                 @OA\Property(property="current", type="string", example="http://yourapp.com/api/users?page=5"),
     *                 @OA\Property(property="previous", type="string", example="http://yourapp.com/api/users?page=4"),
     *                 @OA\Property(property="next", type="string", example="http://yourapp.com/api/users?page=6"),
     *                 @OA\Property(property="first", type="string", example="http://yourapp.com/api/users?page=1"),
     *                 @OA\Property(property="last", type="string", example="http://yourapp.com/api/users?page=10"),
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=5),
     *                 @OA\Property(property="last_page", type="integer", example=10),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=150)
     *                  
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $users = User::with('images')->paginate(15);

        return UserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
