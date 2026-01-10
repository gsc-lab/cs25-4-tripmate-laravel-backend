<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'schedule_item_id' => $this->schedule_item_id,
            'trip_day_id' => $this->trip_day_id,
            'seq_no' => $this->seq_no,
            'visit_time' => $this->string,
            'memo' => $this->memo,
            'place_id' => $this->place_id,

            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
