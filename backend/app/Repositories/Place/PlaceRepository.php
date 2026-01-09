<?php

namespace App\Repositories\Place;

use App\Models\Place;
use App\Models\PlaceCategory;
use App\Repositories\BaseRepository;

class PlaceRepository extends BaseRepository
{
    public function __construct(Place $place)
    {
        parent::__construct($place);
    }

    /**
     * place update 외부 결과를 내부 DB에 저장
     */
    public function update(array $data)
    {
        $categoryCode = $data['category'] ?? 'etc';

        // 카테고리로 해당 id 찾아 반환 (없을 경우 etc id 반환)
        $category = PlaceCategory::where('code', $categoryCode)->first();
        $categoryId = $category ? $category->category_id : 288;

        // 값 Upsert
        $place = $this->model->updateOrCreate(
            ['external_ref' => $data['external_ref']],
            [
                'name' => $data['name'],
                'address' => $data['address'],
                'lat' => $data['lat'],
                'lng' => $data['lng'],
                'category_id' => $categoryId,
            ]
        );

        return $place;
    }
}
