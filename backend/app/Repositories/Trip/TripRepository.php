<?php
namespace App\Repositories\Trip;

use App\Models\Trip;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trip 전용 Repository
 */
class TripRepository extends BaseRepository 
{
  // Trip Model 인스턴스 주입
  public function __construct(Trip $model) 
  {
    parent::__construct($model);
  }


  /**
   * 1. Trip 생성 
   * @param array $data
   * @return Model
   */ 
  public function createTrip(array $data): Model
  {
    return $this->create($data);
  }

  /**
   * 2. user_id로 Trip 목록 조회 (페이지네이션)
   * @param int $userId
   * @param int $page
   * @param int $size
   * @param string|null $sort
   * @param int|null $regionId
   * @return LengthAwarePaginator
   */
  public function paginateTrips(
    int $userId,
    int $page,
    int $size,
    ?string $sort = null,
    ?int $regionId = null
  ): LengthAwarePaginator {

    // 쿼리 빌더 생성
    $query = $this->model->newQuery();

    // user_id 필터링
    $query->where('user_id', $userId);

    // regionId 필터링
    if ($regionId !== null) {
      $query->where('region_id', $regionId);
    }

    // 정렬 옵션 매핑
    $sortOptions = [
      'latest' => ['created_at', 'desc'],
      'oldest' => ['created_at', 'asc'],
      'start_date' => ['start_date', 'asc'],
      'end_date' => ['end_date', 'asc'],
    ];

    // 기본 정렬 설정
    [$column, $direction] = $sortOptions[$sort] ?? ['trip_id', 'desc'];

    // 페이징된 결과 반환
    return $query
      ->orderBy($column, $direction)
      ->paginate(
        $size, 
        ['*'], 
        'page', 
        $page
      );
  }

  /**
   * 3. PK(trip_id) 기준 단일 Trip 조회
   * - 없으면 예외 발생 (ModelNotFoundException)
   * @param int $tripId
   * @return \App\Models\Trip
   * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
   */
  public function findTripOrFail(int $tripId): Trip
  {
    $trip = $this->findOrFail($tripId);

    return $trip;
  }

  /**
   * 4. PK(trip_id) 기준 Trip 부분 업데이트
   * - 없으면 예외 발생 (ModelNotFoundException)
   * - 있으면 해당 레코드 업데이트 후 반환
   * @param int $tripId
   * @param array $data
   * @return \App\Models\Trip
   * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
   */
  public function updateTrip(
    int $tripId, 
    array $data
  ): Trip {
    $trip = $this->updateById($tripId, $data);

    return $trip;
  }

  /**
   * 5. PK(trip_id) 기준 Trip 삭제
   * - 없으면 예외 발생 (ModelNotFoundException)
   * - 있으면 해당 레코드 삭제 후 true 반환
   * @param int $tripId
   * @return bool
   * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
   */
  public function deleteTrip(int $tripId): bool
  {
    return $this->deleteById($tripId);
  }
}