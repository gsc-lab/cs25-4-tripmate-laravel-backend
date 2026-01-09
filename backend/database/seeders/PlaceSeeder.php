<?php

namespace Database\Seeders;

use App\Models\Place;
use App\Models\PlaceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PlaceSeeder extends Seeder
{
    public function run(): void
    {
        $json = File::get(database_path('data/places.json'));
        $places = json_decode($json, true);

        foreach ($places as $placeData) {
            $category = PlaceCategory::where('code', $placeData['category_code'])->first();

            if (! $category) {
                // 해당 카테고리가 없으면 건너뜀
                continue;
            }

            Place::updateOrCreate(
                [
                    'external_provider' => $placeData['external_provider'],
                    'external_ref' => $placeData['external_ref'],
                ],
                [
                    'category_id' => $category->category_id,
                    'name' => $placeData['name'],
                    'address' => $placeData['address'],
                    'lat' => $placeData['lat'] ?? null,
                    'lng' => $placeData['lng'] ?? null,
                ]
            );
        }
    }
}
