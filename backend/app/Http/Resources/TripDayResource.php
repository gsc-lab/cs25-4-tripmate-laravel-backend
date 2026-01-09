<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripDayResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'trip_day_id' => $this->trip_day_id,
            'trip_id' => $this->trip_id,
            'day_no' => $this->day_no,
            'memo' => $this->memo,

            // Trip이 로딩된 경우에만 날짜 계산
            'date' => $this->whenLoaded(
                'trip',
                fn () => $this->trip?->start_date
                    ? $this->trip->start_date
                        ->copy()
                        ->addDays($this->day_no - 1)
                        ->format('Y-m-d')
                    : null
            ),
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}