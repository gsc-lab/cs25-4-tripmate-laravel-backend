<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegionResource extends JsonResource
{
    /**
     * 지역 정보 리소스(지역검색)
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'region_id' => $this->region_id,
            'name' => $this->name,
            'country_code' => $this->country_code,
        ];
    }
}
