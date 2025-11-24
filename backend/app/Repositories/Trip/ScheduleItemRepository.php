<?php
namespace App\Repositories\Trip;

use App\Models\ScheduleItem;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * ScheduleItem 전용 Repository
 * - TripDay 내에서 순번 관리르 위한 쿼리 포함
 */

class ScheduleItemRepository extends BaseRepository
{
    /**
     * scheduleItem Model 인스턴스 주입
     * @param ScheduleItem $model
     */
    public function __construct(ScheduleItem $model)
    {
        parent::__construct($model);
    } 

    /**
     * 1. 특정 TripDay의 ScheduleItem 목록 조회 (페이지네이션)
     * @param int $tripDayId
     * @param int $page
     * @param int $size
     * @return LengthAwarePaginator
     */
    public function paginateSchedulers(
        int $tripDayId,
        int $page,
        int $size
    ): LengthAwarePaginator {
        return $this->model
            ->newQuery()
            ->where('trip_day_id', $tripDayId)
            ->orderBy('seq_no',)
            ->paginate($size, ['*'], 'page', $page);
    }

    /**
     * 2. 특정 TripDay의 ScheduleItem 목록 조회 (페이지네이션 없음)
     * @param int $tripDayId
     * @return Collection
     */
    public function getByTripDayId(int $tripDayId): Collection
    {
        return $this->model
            ->newQuery()
            ->where('trip_day_id', $tripDayId)
            ->orderBy('seq_no', 'asc')
            ->get();
    }

    /**
     * 해당 TripDay 안에 seq_no가 이미 존재하는지 확인
     * @param int $tripDayId
     * @param int $seqNo
     * @return bool
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
     * - 아무것도 없으면 0 반환
     * @param int $tripDayId
     * @return int
     */
    public function getMaxSeqNo(int $tripDayId): int
    {
        return (int)$this->model
            ->newQuery()
            ->where('trip_day_id', $tripDayId)
            ->max('seq_no');
    }

    /**
     * 4. 중간에 ScheduleItem 삽입하기 위한 메서드
     * - seq_no >= fromSeqNo 인 ScheduleItem들의 seq_no 1씩 증가
     * @param int $tripDayId
     * @param int $fromSeqNo
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
     * 5. 특정 seq_no ScheduleItem 삭제 후 뒤에 item 모두 -1 처리
     * @param int $tripDayId
     * @param int $deletedSeqNo  // 삭제된 seq_no
     * @return int               // 영향을 받은 행 수
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
     * 6. scheduleItem 단건 조회
     * @param int $tripDayId
     * @param int $seqNo
     * @return ScheduleItem|null
     */
    public function findByTripDayIdAndSeqNo(
        int $tripDayId,
        int $seqNo
        ): ?ScheduleItem {
            return $this->model
                ->newQuery()
                ->where('trip_day_id', $tripDayId)
                ->where('seq_no', $seqNo)
                ->first();
        }

    /**
     * 7. schedule_item_id 조회
     * @param int $tripDayId
     * @param int $seqNo
     * @return int|null
     */
    public function getScheduleItemId(
        int $tripDayId,
        int $seqNo
        ): ?int {
            $row = $this->model
                ->newQuery()
                ->where('trip_day_id', $tripDayId)
                ->where('seq_no', $seqNo)
                ->first();

            return $row?->schedule_item_id;
        }
    
    /**
     * 8. memo 수정
     * @param int $tripDayId
     * @param int $seqNo
     * @param string|null $memo
     * @return int  영향을 받은 행 수
     */
    public function updateMemo(
        int $tripDayId,
        int $seqNo,
        ?string $memo
        ): int {
            return $this->model
                ->newQuery()
                ->where('trip_day_id', $tripDayId)
                ->where('seq_no', $seqNo)
                ->update(['memo' => $memo]);
        }

    /** 
     * 9. 방문시간 수정 
     * @param int $tripDayId
     * @param int $seqNo
     * @param string|null $visitTime
     * @return int  영향을 받은 행 수
     */
    public function updateVisitTime(
        int $tripDayId,
        int $seqNo,
        $visitTime
        ): int {
            return $this->model
                ->newQuery()
                ->where('trip_day_id', $tripDayId)
                ->where('seq_no', $seqNo)
                ->update(['visit_time' => $visitTime]);
        }

    /**
     * 10. 해당 TripDay의 ScheduleItem 개수 조회
     * @param int $tripDayId
     * @return int
     */
    public function countByTripDayId(int $tripDayId): int
    {
        return $this->model
            ->newQuery()
            ->where('trip_day_id', $tripDayId)
            ->count();
    }

    /**
     * 11. 단일 ScheduleItem의 seq_no 업데이트
     * @param int $tripDayId
     * @param int $oldSeqNo // 기존 seq_no
     * @param int $newSeqNo // 새로운 seq_no
     * @return int          // 업데이트 된 row 수
     */
    public function updateSeqNo(
        int $tripDayId,
        int $oldSeqNo,
        int $newSeqNo
        ): int {
            return $this->model
                ->newQuery()
                ->where('trip_day_id', $tripDayId)
                ->where('seq_no', $oldSeqNo)
                ->update(['seq_no' => $newSeqNo]);
        }

    /**
     * 12. 메모 + 방문시간 동시 수정
     * @param int $tripDayId
     * @param int $seqNo
     * @param string|null $memo
     * @param string|null $visitTime
     * @return int  영향을 받은 행 수
     */
    public function updateMemoAndVisitTime(
        int $tripDayId,
        int $seqNo,
        ?string $memo,
        ?string $visitTime
    ): int {
        $data = [];

        // 수정할 값이 null이 아닐 때만 배열에 추가
        if (!is_null($memo)) {
            $data['memo'] = $memo;
        }
        if (!is_null($visitTime)) {
            $data['visit_time'] = $visitTime;
        }

        // 수정 할 데이터가 없으면 0 반환
        if (empty($data)) {
            return 0; 
        }

        return $this->model
            ->newQuery()
            ->where('trip_day_id', $tripDayId)
            ->where('seq_no', $seqNo)
            ->update($data);
    }

    /**
     * 13. 재배치(아래로 이동)용 메서드
     * - oldSeqNo < newSeqNo 인 경우
     * - (oldSeqNo, newSeqNo] 구간의 항목들을 seq_no - 1
     * @param int $tripDayId
     * @param int $oldSeqNo
     * @param int $newSeqNo
     * @return int 
     */
    public function decrementSeqRange(
        int $tripDayId,
        int $oldSeqNo,
        int $newSeqNo
    ): int {
        return $this->model
            ->newQuery()
            ->where('trip_day_id', $tripDayId)
            ->where('seq_no', '>', $oldSeqNo)
            ->where('seq_no', '<=', $newSeqNo)
            ->decrement('seq_no');
    }

    /**
     * 14. 재배치(위로 이동)용 메서드
     * - oldSeqNo > newSeqNo 인 경우
     * - [newSeqNo, oldSeqNo) 구간의 항목들을 seq_no + 1
     * @param int $tripDayId
     * @param int $oldSeqNo
     * @param int $newSeqNo
     * @return int 
     */
    public function incrementSeqRange(
        int $tripDayId,
        int $oldSeqNo,
        int $newSeqNo
    ): int {
        return $this->model
            ->newQuery()
            ->where('trip_day_id', $tripDayId)
            ->where('seq_no', '>=', $newSeqNo)
            ->where('seq_no', '<', $oldSeqNo)
            ->increment('seq_no');
    }
}