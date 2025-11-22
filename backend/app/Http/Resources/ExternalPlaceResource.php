<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExternalPlaceResource extends JsonResource
{
    /**
     * 장소 리소스 - 외부 API 기반
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // google에서 받아올 배열
        return [
            'place_id' => $this['place_id'], 
            'name' => $this['name'],
            'address' => $this['formatted_address'] ?? $this['vicinity'],
            'lat' => $this['geometry']['location']['lat'],
            'lng' => $this['geometry']['location']['lng'],
            'category' => $this['types'][0] ?? 'unknown', 
        ];
    }
}
