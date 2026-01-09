<?php

namespace Database\Seeders;

use App\Models\PlaceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PlaceCategorySeeder extends Seeder
{
    /**
     * Run the PlaceCategory database seeds.
     */
    public function run(): void
    {
        // json 파일 찾기
        $json = File::get(database_path('data/place_categories.json'));
        $categories = json_decode($json, true);

        // 데이터 삽입
        foreach ($categories as $category) {
            PlaceCategory::updateOrCreate(
                ['code' => $category['code']],
                ['name' => $category['name']]
            );
        }
    }
}
