<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'age' => $this->age,
            'location' => $this->location,
            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(function ($image) {
                    return $image->image_url ;
                });
            }),
            'primary_image' => $this->whenLoaded('images', function () {
                $primaryImage = $this->images->where('is_primary', true)->first();
                return $primaryImage ? $primaryImage->image_url : ($this->images->first()?->image_url ?? $this->image);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
