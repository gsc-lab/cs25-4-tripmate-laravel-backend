<?php
namespace App\Repositories\Trip;

use App\Models\TripDay;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

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
     * 1. 특정 Trip의 TripDay 목록 조회 (페이지네이션)
     */
    public function paginateByTripId(
        int $tripId,
        int $page,
        int $size
    ): LengthAwarePaginator {
        return $this->model
            ->newQuery()
            ->where('trip_id', $tripId)
            ->orderBy('day_no', 'asc')
            ->paginate($size, ['*'], 'page', $page);
    }

    /**
     * 2. 해당 Trip 안에 day_no가 이미 존재하는지 확인
     */
    public function existDayNo(int $tripId, int $dayNo): bool
    {
        return $this->model
            ->newQuery()
            ->where('trip_id', $tripId)
            ->where('day_no', $dayNo)
            ->exists();
    }
    /**
     * 3. 중간 삽입용: fromDayNo 이상인 day_no를 +1 증가
     * @return int 영향을 받은 row 수
     */
    public function incrementDayNoFrom(int $tripId, int $fromDayNo): int
    {
        $rows = $this->model
        ->newQuery()
        ->where('trip_id', $tripId)
        ->where('day_no', '>=', $fromDayNo)
        ->orderByDesc('day_no') 
        ->lockForUpdate()  // 동시성 방지
        ->get();

        foreach ($rows as $row) {
            $row->increment('day_no');
        }

        return $rows->count();
    }

    /**
     * 4. 특정 day_no를 삭제 한 후 뒤의 일차들을 -1 씩 감소를 위한 메서드
     * - 연속성 유지를 위해 사용
     *
     * @param  int  $deleteDayNo  // 삭제 된 day_no
     * @return int // 영향을 받은 row 수
     */
    public function decrementDayNoAfter(int $tripId, int $fromDayNo): int
    {
        // 해당 일차 이후의 row 들 조회
        $rows = $this->model
            ->newQuery()
            ->where('trip_id', $tripId)
            ->where('day_no', '>', $fromDayNo)
            ->orderBy('day_no', 'asc')
            ->lockForUpdate() // 동시성 방지
            ->get();

        // 각 row 들의 day_no 감소
        foreach ($rows as $row) {
            $row->decrement('day_no');
        }

        return $rows->count();
    }

    /**
     * 5. TripDay 단건 조회 (nullable)
     */
    public function findByTripIdAndDayNo(int $tripId, int $dayNo): ?TripDay
    {
        return $this->model
            ->newQuery()
            ->where('trip_id', $tripId)
            ->where('day_no', $dayNo)
            ->first();
    }

    /**
     * 6. TripDay 단건 조회 (없으면 예외)
     *
     * @throws ModelNotFoundException
     */
    public function findByTripIdAndDayNoOrFail(int $tripId, int $dayNo): TripDay
    {
        $day = $this->findByTripIdAndDayNo($tripId, $dayNo);

        if (! $day) {
            throw new ModelNotFoundException('해당 일차가 존재하지 않습니다');
        }

        return $day;
    }

    /**
     * 7. memo 수정
     * @return int // 영향을 받은 row 수
     */
    public function updateMemoByTripIdAndDayNo(
        int $tripId,
        int $dayNo,
        ?string $memo
    ): int {
        return $this->model
            ->newQuery()
            ->where('trip_id', $tripId)
            ->where('day_no', $dayNo)
            ->update(['memo' => $memo]);
    }

    /**
     * 8. 해당 Trip에 속한 TripDay 개수 반환
     */
    public function countByTripId(int $tripId): int
    {
        return $this->model
            ->newQuery()
            ->where('trip_id', $tripId)
            ->count();
    }

    /**
     *  9. 특정 Trip의 모든 day_no를 임시 큰 값으로 변경
     * - 재배치 작업 전 충돌 방지용
     * - +1000 씩 증가
     * @return int // 영향을 받은 row 수
     */
    public function tempShiftDayNo(
        int $tripId,
        int $offset = 1000
    ): int {
        return $this->model
            ->newQuery()
            ->where('trip_id', $tripId)
            ->update([
                'day_no' => DB::raw("day_no + {$offset}"),
            ]);
    }

    /**
     * 10. trip_Day_id 기준으로 day_no 조정
     * - 재배치 작업 후 실제 일차 번호로 복원
     * @return int // 영향을 받은 row 수
     */
    public function updateDayNoByTripDayId(
        int $tripId,
        int $tripDayId,
        int $newDayNo
    ): int {
        return $this->model
            ->newQuery()
            ->where('trip_id', $tripId)
            ->where('trip_day_id', $tripDayId)
            ->update(['day_no' => $newDayNo]);
    }

    /**
     * 11. 모든 trip_day_id가 trip에 속하는지 확인
     * @param  array<int>  $tripDayIds
     */
    public function countByTripAndTripDayIds(
        int $tripId,
        array $tripDayIds
    ): int {
        if (empty($tripDayIds)) {
            return 0;
        }

        return $this->model
            ->newQuery()
            ->where('trip_id', $tripId)
            ->whereIn('trip_day_id', $tripDayIds)
            ->count();
    }

    /**
    * 12. Trip + day_no로 trip_day_id 조회
    * - 없으면 null 반환
    */
    public function getTripDayId(int $tripId, int $dayNo): ?int
    {
        return $this->model
            ->newQuery()
            ->where('trip_id', $tripId)
            ->where('day_no', $dayNo)
            ->value('trip_day_id');
    }
}