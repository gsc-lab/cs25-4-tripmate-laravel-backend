<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class RegionSeeder extends Seeder
{
    /**
     * Run the regions database seeds.
     */
    public function run(): void
    {
        // JSON 파일 읽기
        $json = File::get(database_path('data/regions.json'));
        $regions = json_decode($json, true);

        // 반복문으로 데이터 삽입
        foreach ($regions as $region) {
            Region::firstOrCreate(
                [
                    'name' => $region['name'],
                    'country_code' => $region['country_code'],
                ]
            );
        }
    }
}
