<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExternalPlaceResource extends JsonResource
{
    /**
     * 장소 리소스 - 외부 API 기반
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // google에서 받아올 배열
        return [
            'place_id' => $this['id'],
            'name' => $this['displayName']['text'] ?? $this['name'],
            'address' => $this['formattedAddress'],
            'lat' => $this['location']['latitude'],
            'lng' => $this['location']['longitude'],
            'category' => $this['primaryType'] ?? 'etc',
        ];
    }
}
