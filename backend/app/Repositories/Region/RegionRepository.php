<?php
    namespace App\Repositories\Region;

    use App\Repositories\BaseRepository;
    use App\Models\Region;

    class RegionRepository extends BaseRepository
    {
        public function __construct(Region $region)
        {
            parent::__construct($region);
        }

        /**
         * find Regions
         * @param mixed $query
         * @return \Illuminate\Database\Eloquent\Collection|\App\Models\Region[]
         */
        public function findRegions($query)
        {
            return $this->model->where('name', 'like', '%'. $query .'%')->get();
        }

        /**
         * selected Regions
         * @param mixed $country
         * @return \Illuminate\Database\Eloquent\Collection|\App\Models\Region[]
         */
        public function selectRegions($country)
        {
            return $this->model->where("country_code", $country)->get();
        }
    }
