<?php

namespace Database\Seeders;

use App\Models\Place;
use App\Models\ScheduleItem;
use App\Models\TripDay;
use Illuminate\Database\Seeder;

class ScheduleItemSeeder extends Seeder
{
    public function run(): void
    {
        $tripDays = TripDay::orderBy('trip_day_id')->get();
        $places = Place::orderBy('place_id')->get();

        if ($tripDays->isEmpty()) {
            return;
        }

        // place가 하나도 없으면 place_id는 전부 null로 넣음
        $placeCount = $places->count();
        $placeIndex = 0;

        foreach ($tripDays as $index => $tripDay) {

            // 모든 day에 최소 2개, 짝수번째 day에는 3개
            $itemsPerDay = ($index % 2 === 0) ? 3 : 2;

            for ($seq = 1; $seq <= $itemsPerDay; $seq++) {
                $placeId = null;

                if ($placeCount > 0) {
                    $place = $places[$placeIndex % $placeCount];
                    $placeId = $place->place_id;
                    $placeIndex++;
                }

                // visit_time
                $visitTimes = ['09:00:00', '13:00:00', '19:00:00'];
                $visitTime = $visitTimes[($seq - 1) % count($visitTimes)];

                ScheduleItem::updateOrCreate(
                    [
                        'trip_day_id' => $tripDay->trip_day_id,
                        'seq_no' => $seq,
                    ],
                    [
                        'place_id' => $placeId,
                        'visit_time' => $visitTime,
                        'memo' => "{$tripDay->day_no}일차 - 일정 {$seq}",
                    ]
                );
            }
        }
    }
}
