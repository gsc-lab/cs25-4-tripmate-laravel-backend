<?php

namespace App\Repositories\Region;

use App\Models\Region;
use App\Repositories\BaseRepository;

class RegionRepository extends BaseRepository
{
    public function __construct(Region $region)
    {
        parent::__construct($region);
    }

    /**
     * find Regions
     *
     * @param  mixed  $query
     * @param  mixed  $country
     * @return \Illuminate\Database\Eloquent\Collection|\App\Models\Region[]
     */
    public function findRegions($query, $country)
    {
        return $this->model
            ->where('country_code', $country)
            ->where('name', 'like', '%'.$query.'%')
            ->get();
    }

    /**
     * selected Regions
     *
     * @param  mixed  $country
     * @return \Illuminate\Database\Eloquent\Collection|\App\Models\Region[]
     */
    public function selectRegions($country)
    {
        return $this->model->where('country_code', $country)->get();
    }
}
