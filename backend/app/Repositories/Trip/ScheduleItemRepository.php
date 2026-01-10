<?php

namespace App\Repositories\Trip;

use App\Models\ScheduleItem;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * ScheduleItem 전용 Repository
 * - TripDay 내에서 순번 관리르 위한 쿼리 포함
 */
class ScheduleItemRepository extends BaseRepository
{
    /**
     * scheduleItem Model 인스턴스 주입
     */
    public function __construct(ScheduleItem $model)
    {
        parent::__construct($model);
    }

    /**
     * 1. 특정 TripDay의 ScheduleItem 목록 조회 (페이지네이션)
     */
    public function paginateByTripDayId(
        int $tripDayId,
        int $page,
        int $size
    ): LengthAwarePaginator {
        return $this->model
            ->newQuery()
            ->where('trip_day_id', $tripDayId)
            ->orderBy('seq_no', 'asc')
            ->paginate($size, ['*'], 'page', $page);
    }

    /**
     * 2. 해당 TripDay 안에 seq_no가 이미 존재하는지 확인
     */
    public function existsSeqNo(int $tripDayId, int $seqNo): bool
    {
        return $this->model
            ->newQuery()
            ->where('trip_day_id', $tripDayId)
            ->where('seq_no', $seqNo)
            ->exists();
    }

    /**
     * 3. 특정 TripDay에서 가장 큰 seq_no 조회
     */
    public function getMaxSeqNo(int $tripDayId): int
    {
        return (int) $this->model
            ->newQuery()
            ->where('trip_day_id', $tripDayId)
            ->max('seq_no');
    }

    /**
     * 4. 중간에 ScheduleItem 삽입하기 위한 메서드
     * - seq_no >= fromSeqNo 인 ScheduleItem들의 seq_no 1씩 증가
     *
     * @return int 영향을 받은 행 수
     */
    public function incrementSeqNos(int $tripDayId, int $fromSeqNo): int
    {
        return $this->model
            ->newQuery()
            ->where('trip_day_id', $tripDayId)
            ->where('seq_no', '>=', $fromSeqNo)
            ->increment('seq_no');
    }

    /**
     * 5. 삭제 후 seq_no 정리
     * - seq_no > deletedSeqNo → -1
     */
    public function decrementSeqNos(
        int $tripDayId,
        int $fromSeqNo
    ): int {
        return $this->model
            ->newQuery()
            ->where('trip_day_id', $tripDayId)
            ->where('seq_no', '>', $fromSeqNo)
            ->decrement('seq_no');
    }


    /**
     * 6. schedule_item_id 목록으로 조회
     * @param int[] $itemIds
     */
    public function getByItemIds(array $itemIds): Collection
    {
        return $this->model
            ->newQuery()
            ->whereIn('schedule_item_id', $itemIds)
            ->get();
    }

    /**
     * 7. latlng를 가져오는 헬퍼 메서드
     * - lat와 lng를 한 쌍으로 반환
     *
     * @param  mixed  $tripDayId
     * @return float[]
     */
    public function getlatlngFromPlaceId($tripDayId)
    {
        $items = ScheduleItem::with('place:place_id,lat,lng')
            ->where('trip_day_id', $tripDayId)
            ->orderBy('seq_no', 'asc')
            ->get();

        $latlng = [];
        foreach ($items as $item) {
            $latlng[] = [
                'lat' => $item->place->lat,
                'lng' => $item->place->lng,
            ];
        }

        return $latlng;
    }

    /**
     * 8. 재배치 전 충돌 방지용 seq_no 임시 이동
     * - 재배치 작업 전 충돌 방지용
     * - +1000 씩 증가
     * @param int[] $tripDayIds
     */
    public function tempShiftSeqNos(
        array $tripDayIds,
        int $offset = 1000
    ): int {
        if (empty($tripDayIds)) {
            return 0;
        }

        return $this->model
            ->newQuery()
            ->whereIn('trip_day_id', $tripDayIds)
            ->update([
                'seq_no' => DB::raw('seq_no + ' . $offset),
            ]);
    }

    /**
     * 9. 특정 TripDay에 item_ids 순서대로 seq_no 재배치
     * @param  int[]  $itemIds  // 재배치할 schedule_item_id 배열
     */
    public function reorderSeqNosByItemIds(
        int $tripDayId,
        array $itemIds,
    ): void {
        foreach (array_values($itemIds) as $index => $itemId) {
            $this->model
                ->newQuery()
                ->where('schedule_item_id', $itemId)
                ->update([
                    'trip_day_id' => $tripDayId,
                    'seq_no' => $index + 1,
                ]);
        }
    }

    /**
     * 10. 특정 tripDay에 남아있는 아이템 seq_no 재정렬
     */
    public function normalizeSeqNosForTripDay(int $tripDayId): void
    {
        $items = $this->model
            ->newQuery()
            ->where('trip_day_id', $tripDayId)
            ->orderBy('seq_no', 'asc')
            ->get(['schedule_item_id']);

        foreach ($items as $index => $item) {
            $this->model
                ->newQuery()
                ->where('schedule_item_id', $item->schedule_item_id)
                ->update(['seq_no' => $index + 1]);
        }
    }
}