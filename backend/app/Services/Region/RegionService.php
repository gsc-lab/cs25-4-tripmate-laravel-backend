<?php

namespace App\Services\Region;

use App\Repositories\Region\RegionRepository;

class RegionService
{
    private RegionRepository $regionRepository;

    public function __construct(RegionRepository $regionRepository)
    {
        $this->regionRepository = $regionRepository;
    }

    /**
     * Regions Service
     *
     * @param  mixed  $country
     * @param  mixed  $query
     */
    public function regions($country = 'KR', $query = null)
    {
        // 지역 검색
        if ($query !== null) {
            return $this->regionRepository->findRegions($query, $country);

        } else {
            // 지역 목록 반환
            return $this->regionRepository->selectRegions($country);
        }
    }
}
