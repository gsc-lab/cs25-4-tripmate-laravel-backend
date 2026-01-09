<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class TripSeeder extends Seeder
{
    public function run(): void
    {
        $json = File::get(database_path('data/trips.json'));
        $trips = json_decode($json, true);

        foreach ($trips as $tripData) {
            // 유저 찾기
            $user = User::where('email_norm', $tripData['user_email'])->first();
            if (! $user) {
                // 해당 유저가 없으면 건너뜀
                continue;
            }

            // Region 랜덤 선택
            $region = Region::inRandomOrder()->first();

            if (! $region) {
                // Region 데이터가 하나도 없으면 이 Trip은 생성 안 함
                continue;
            }

            // 3) Trip 생성/업데이트
            Trip::updateOrCreate(
                [
                    'user_id' => $user->user_id,
                    'title' => $tripData['title'],
                    'start_date' => $tripData['start_date'],
                    'end_date' => $tripData['end_date'],
                ],
                [
                    'region_id' => $region->region_id,
                ]
            );
        }
    }
}
