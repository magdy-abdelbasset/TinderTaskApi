<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LikeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'from_user_id' => $this->from_user_id,
            'to_user_id' => $this->to_user_id,
            'from_user' => $this->whenLoaded('fromUser', function () {
                $primaryImage = $this->fromUser->images->where('is_primary', true)->first();
                return [
                    'id' => $this->fromUser->id,
                    'name' => $this->fromUser->name,
                    'image' => $this->fromUser->image,
                    'primary_image' => $primaryImage?->image_url ?? $this->fromUser->image,
                ];
            }),
            'to_user' => $this->whenLoaded('toUser', function () {
                $primaryImage = $this->toUser->images->where('is_primary', true)->first();
                return [
                    'id' => $this->toUser->id,
                    'name' => $this->toUser->name,
                    'image' => $this->toUser->image,
                    'primary_image' => $primaryImage?->image_url ?? $this->toUser->image,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
