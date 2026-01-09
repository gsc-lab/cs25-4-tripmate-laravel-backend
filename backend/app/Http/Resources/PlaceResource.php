<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlaceResource extends JsonResource
{
    /**
     * 장소 리소스(외부 결과 저장, 단건 조회)
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'place_id' => $this->place_id,
            'external_ref' => $this->external_ref,
            'name' => $this->name,
            'address' => $this->address,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'category_id' => $this->category_id,

            // JOIN한 경우 category 반환
            'category' => $this->whenLoaded('category', function () {
                return $this->category->name;
            }),
        ];
    }
}
