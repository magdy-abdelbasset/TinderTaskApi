<?php

namespace App\Http\Controllers\Api;

use App\Events\UserLiked;
use App\Http\Controllers\Controller;
use App\Http\Resources\LikeResource;
use App\Models\Like;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/likes",
     *     tags={"Likes"},
     *     summary="Get likes for a specific user",
     *     description="Returns paginated list of likes received by a specific user",
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="ID of the user to get likes for",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
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
     *                     @OA\Property(property="from_user_id", type="integer", nullable=true, example=1),
     *                     @OA\Property(property="to_user_id", type="integer", example=2),
     *                     @OA\Property(property="from_user", type="object", nullable=true,
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="image", type="string", example="https://picsum.photos/400/600?random=123")
     *                     ),
     *                     @OA\Property(property="to_user", type="object",
     *                         @OA\Property(property="id", type="integer", example=2),
     *                         @OA\Property(property="name", type="string", example="Jane Doe"),
     *                         @OA\Property(property="image", type="string", example="https://picsum.photos/400/600?random=456")
     *                     ),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request - user_id parameter is required",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="user_id is required")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $userId = $request->query('user_id');
        
        if (!$userId) {
            return response()->json(['error' => 'user_id is required'], 400);
        }

        $likes = Like::with(['fromUser.images', 'toUser.images'])
            ->where('to_user_id', $userId)
            ->paginate(15);

        return LikeResource::collection($likes);
    }

    /**
     * @OA\Post(
     *     path="/api/likes",
     *     tags={"Likes"},
     *     summary="Create a new like",
     *     description="Create a like from one user to another (or anonymous like)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"to_user_id"},
     *             @OA\Property(property="from_user_id", type="integer", nullable=true, example=1, description="ID of user giving the like (null for anonymous)"),
     *             @OA\Property(property="to_user_id", type="integer", example=2, description="ID of user receiving the like")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Like created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="from_user_id", type="integer", nullable=true, example=1),
     *             @OA\Property(property="to_user_id", type="integer", example=2),
     *             @OA\Property(property="from_user", type="object", nullable=true),
     *             @OA\Property(property="to_user", type="object"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Like already exists",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Like already exists")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'from_user_id' => 'nullable|exists:users,id',
            'to_user_id' => 'required|exists:users,id',
        ]);

        try {
            $like = Like::create([
                'from_user_id' => $request->from_user_id,
                'to_user_id' => $request->to_user_id,
            ]);

            // Dispatch event when user gets liked
            $toUser = User::find($request->to_user_id);
            UserLiked::dispatch($toUser, $like);

            return response()->json(new LikeResource($like->load(['fromUser.images', 'toUser.images'])), 201);
        } catch (\Exception $e) {
            // Handle duplicate like attempts
            return response()->json(['error' => 'Like already exists'], 409);
        }
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
