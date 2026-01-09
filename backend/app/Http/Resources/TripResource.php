<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripResource extends JsonResource
{
    /**
     * 여행 관련 리소스
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'trip_id' => $this->trip_id,
            'title' => $this->title,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'day_count' => $this->day_count,
            'region_id' => $this->region_id,

            // 만일 WITH으로 지역 이름을 가져올 경우
            'region_name' => $this->whenLoaded(
                'region',
                fn () => $this->region?->name
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}