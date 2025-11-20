<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon; // 날짜 클래스

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

            // 해당 날짜를 반환할 경우
            'date' => $this->whenLoaded('trip', function () {
                // trip의 시작일 + (day_no - 1)일
                $startDate = Carbon::parse($this->trip->start_date);
                return $startDate->addDays($this->day_no - 1)->format('Y-m-d'); // startDate 객체와 더한 후 포맷
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
