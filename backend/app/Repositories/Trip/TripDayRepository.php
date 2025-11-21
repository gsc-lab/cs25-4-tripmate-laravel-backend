<?php
namespace App\Repositories\Trip;

use App\Models\TripDay;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * TripDay 전용 Repository
 */
class TripDayRepository extends BaseRepository
{
  // TripDay Model 인스턴스 주입
  public function __construct(TripDay $model) 
  {
    parent::__construct($model);
  }

  /**
   * 1. TripDay 생성
   * @param array $data
   * @return Model
   */
  public function createTripDay(array $data): Model
  {
    return $this->create($data);
  }

  /**
   * 2. trip_id로 TripDay 목록 조회 (페이지네이션 없음)
   * @param int $tripId
   * @return Collection
   */
  public function getTripDaysByTripId(int $tripId): Collection
  {
    return $this->all()->where('trip_id', $tripId);
  }

  /**
   * 3. trip_id로 TripDay 단일 조회
   * @param int $tripId
   * @return Model|null
   */
  public function getFirstTripDayByTripId(int $tripId): ?Model
  {
    
  }
}