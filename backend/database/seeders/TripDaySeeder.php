<?php

namespace Database\Seeders;

use App\Models\Trip;
use App\Models\TripDay;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TripDaySeeder extends Seeder
{
    public function run(): void
    {
        $trips = Trip::all();

        foreach ($trips as $trip) {
            // 이미 TripDay가 있으면 중복 생성 안 함
            if (TripDay::where('trip_id', $trip->trip_id)->exists()) {
                continue;
            }

            $start = Carbon::parse($trip->start_date);
            $end = Carbon::parse($trip->end_date);
            $days = $start->diffInDays($end) + 1;

            for ($i = 1; $i <= $days; $i++) {
                TripDay::updateOrCreate(
                    [
                        'trip_id' => $trip->trip_id,
                        'day_no' => $i,
                    ],
                    [
                        'memo' => "{$trip->title} - {$i}일차",
                    ]
                );
            }
        }
    }
}
