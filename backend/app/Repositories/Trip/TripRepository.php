<?php

namespace App\Repositories\Trip;

use App\Models\Trip;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

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
     * @param array{title:string, region_id:int, start_date:string, end_date:string, user_id:int} $data
     */
    public function createTrip(array $data): Model
    {
        /** @var Trip $trip */
        $trip = $this->create($data);

        return $trip;
    }

    /**
     * 2. user_id로 Trip 목록 조회 (페이지네이션)
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
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findTripOrFail(int $tripId): Trip
    {
        /** @var Trip $trip */
        $trip = $this->findOrFail($tripId);

        return $trip;
    }

    /**
     * 4. PK(trip_id) 기준 Trip 부분 업데이트
     * - 없으면 예외 발생 (ModelNotFoundException)
     * - 있으면 해당 레코드 업데이트 후 반환
     * @param array{title?:string, region_id?:int, start_date?:string, end_date?:string} $data
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateTrip(int $tripId, array $data): Trip 
    {
        /** @var Trip $trip */
        $trip = $this->updateById($tripId, $data);

        return $trip;
    }

    /**
     * 5. PK(trip_id) 기준 Trip 삭제
     * - 없으면 예외 발생 (ModelNotFoundException)
     * - 있으면 해당 레코드 삭제 후 true 반환
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteTrip(int $tripId): bool
    {
        return $this->deleteById($tripId);
    }
}