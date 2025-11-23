<?php

namespace App\Services\ScheduleItem;

use App\Models\Trip;
use App\Models\ScheduleItem;
use App\Repositories\Trip\TripDayRepository;
use App\Repositories\ScheduleItem\ScheduleItemRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScheduleItemService
{
    // repository 프로퍼티
    protected ScheduleItemRepository $scheduleItemRepository;
    protected TripDayRepository $tripDayRepository;

    // 생성자에서 repository 주입
    public function __construct(
        ScheduleItemRepository $scheduleItemRepository,
        TripDayRepository $rsvpDayRepository
        ) {
            $this->scheduleItemRepository = $scheduleItemRepository;
            $this->tripDayRepository = $rsvpDayRepository;
        }
    
    /**
    * 내부 공통 헬퍼 메서드
    * - Trip + day_no로 Trip_day_id 조회
    * - 없으면 ModelNotFoundException 예외 발생
    * @param Trip $trip
    * @param int $dayNo
    * @return int
    * @throws ModelNotFoundException
    */
    protected function getTripDayIdOrFail(
        Trip $trip, 
        int $dayNo
        ): int {
            
            // Trip_day_id 조회
            $tripDayId = $this->tripDayRepository->getTripDayId(
                $trip->trip_id,
                $dayNo
            );

            // 없으면 예외 발생
            if (is_null($tripDayId)) {
                throw new ModelNotFoundException("해당하는 Trip Day를 찾을 수 없습니다");
            }

            // Trip_day_id 반환
            return $tripDayId;
        }
    
    /**
     * 내부 공통 헬퍼 메서드
     * - schedule_item_id로 ScheduleItem 조회
     * - Trip + day_no에 속하는지까지 확인
     *
     * @param Trip $trip
     * @param int $dayNo
     * @param int $itemId   schedule_item_id
     * @return ScheduleItem
     * @throws ModelNotFoundException
     */
    protected function getOwnedScheduleItemOrFail(
        Trip $trip,
        int $dayNo,
        int $itemId
    ): ScheduleItem {
        // TripDay 존재 여부 및 trip_day_id 조회
        $tripDayId = $this->getTripDayIdOrFail($trip, $dayNo);

        // PK(scheduled_item_id)로 조회
        /** @var ScheduleItem|null $item */
        $item = $this->scheduleItemRepository->findById($itemId);

        // 없거나 다른 TripDay에 속하면 예외
        if (!$item || $item->trip_day_id !== $tripDayId) {
            throw new ModelNotFoundException('해당하는 Schedule Item을 찾을 수 없습니다');
        }

        return $item;
    }
    
    /**
     * 1. 특정 TripDay의 ScheduleItem 목록 조회 (페이지네이션)
     * @param Trip $trip
     * @param int $dayNo
     * @param int $page
     * @param int $size
     * @return LengthAwarePaginator
     * @throws ModelNotFoundException
     */
    public function paginateScheduleItems(
        Trip $trip,
        int $dayNo,
        int $page,
        int $size
    ): LengthAwarePaginator {
        // Trip_day_id 조회
        $tripDayId = $this->getTripDayIdOrFail($trip, $dayNo);

        // ScheduleItem 목록 페이지네이션 조회
        return $this->scheduleItemRepository->paginateSchedulers(
            $tripDayId,
            $page,
            $size
        );
    }

    /**
     * 2. 특정 TripDay의 ScheduleItem 전체 목록 조회 (페이지네이션 없음)
     * @param Trip $trip
     * @param int $dayNo
     * @return Collection
     * @throws ModelNotFoundException
     */
    public function listScheduleItems(
        Trip $trip,
        int $dayNo
    ): Collection {
        // Trip_day_id 조회
        $tripDayId = $this->getTripDayIdOrFail($trip, $dayNo);

        // ScheduleItem 전체 목록 조회
        return $this->scheduleItemRepository->getByTripDayId(
            $tripDayId
        );
    }

    /**
     * 3. ScheduleItem 생성
     * - seq_no가 null이면 해당 TripDay의 마지막 seq_no + 1로 설정
     * - seq_no가 있으면 해당 seq_no 이후의 항목들의 seq_no 1씩 증가
     * @param Trip $trip
     * @param int $dayNo
     * @param int|null $seqNo
     * @param int|null $placeId 
     * @param string|null $visitTime
     * @param string|null $memo
     * @return ScheduleItem
     * @throws ModelNotFoundException
     */
    public function createScheduleItem(
        Trip $trip, 
        int $dayNo,
        ?int $seqNo,
        ?int $placeId,
        ?string $visitTime,
        ?string $memo
        ): ScheduleItem {
            // Trip_day_id 조회
            $tripDayId = $this->getTripDayIdOrFail($trip, $dayNo);

            return DB::transaction(function () use (
                $tripDayId,
                $seqNo,
                $placeId,
                $visitTime,
                $memo
            ) {

                // 현재 최대 seq_no 조회
                $maxSeqNo = $this->scheduleItemRepository->getMaxSeqNo($tripDayId);

                // seq_no가 null이면 마지막 seq_no + 1로 설정
                if (is_null($seqNo)) {
                    $seqNo = $maxSeqNo + 1;
                } else {
                    // 최소값 보정 (1보다 작으면 1로 설정)
                    if ($seqNo < 1) {
                        $seqNo = 1;
                    }

                    // 최대값 보정 (현재 maxSeqNo + 1 보다 크면 맨 뒤)
                    if ($seqNo > $maxSeqNo + 1) {
                        $seqNo = $maxSeqNo + 1;
                    }

                    // 이미 해당 seq_no가 존재하면 이후 항목들의 seq_no 1씩 증가
                    if ($this->scheduleItemRepository->existsSeqNo(
                        $tripDayId,
                        $seqNo
                    )) {
                        $this->scheduleItemRepository->incrementSeqNos(
                            $tripDayId,
                            $seqNo
                        );
                    }
                }

                // ScheduleItem 생성
                $item = $this->scheduleItemRepository->create([
                    'trip_day_id' => $tripDayId,
                    'place_id' => $placeId,
                    'seq_no' => $seqNo,
                    'visit_time' => $visitTime,
                    'memo' => $memo
                ]);

                return $item;
        });

    }

    /**
     * 4. ScheduleItem 단건 조회
     * @param Trip $trip
     * @param int $dayNo
     * @param int $itemId  schedule_item_id
     * @return ScheduleItem
     * @throws ModelNotFoundException
     */
    public function getScheduleItem(
        Trip $trip,
        int $dayNo,
        int $itemId
    ): ScheduleItem {
        return $this->getOwnedScheduleItemOrFail($trip, $dayNo, $itemId);
    }

    /**
     * 5. ScheduleItem 메모/방문시간 수정
     * = 둘 중 일부만 수정 가능
     * @param Trip $trip
     * @param int $dayNo
     * @param int $itemId       
     * @param string|null $visitTime
     * @param string|null $memo
     * @return ScheduleItem
     * @throws ModelNotFoundException
     */
    public function updateScheduleItem(
        Trip $trip,
        int $dayNo,
        int  $itemId,
        ?string $visitTime,
        ?string $memo
    ): ScheduleItem {
        // tripDayId 조회
        $item = $this->getOwnedScheduleItemOrFail($trip, $dayNo, $itemId);

        // 메모/방문시간 수정
        if (!is_null($visitTime)) {
            $item->visit_time = Carbon::parse($visitTime);
        }
        if (!is_null($memo)) {
            $item->memo = $memo;
        }

        $item->save();

        return $item;
    }

    /**
     * 6. ScheduleItem 삭제
     * - 이후 항목들의 seq_no 1씩 감소
     * @param Trip $trip
     * @param int $dayNo
     * @param int $seqNo
     * @return void
     * @throws ModelNotFoundException
     */
    public function deleteScheduleItem(
        Trip $trip,
        int $dayNo,
        int $seqNo
    ): void {
        // tripDayId 조회
        $tripDayId = $this->getTripDayIdOrFail($trip, $dayNo);

        DB::transaction(function () use ($tripDayId, $seqNo) {

            // ScheduleItem 삭제 대상 조회
            $item = $this->scheduleItemRepository->findByTripDayIdAndSeqNo(
                $tripDayId,
                $seqNo
            );

            // 없으면 예외 발생
            if (is_null($item)) {
                throw new ModelNotFoundException("해당하는 Schedule Item을 찾을 수 없습니다");
            }

            // ScheduleItem 삭제
            $item->delete();

            // 이후 항목들의 seq_no 1씩 감소
            $this->scheduleItemRepository->decrementSeqNos(
                $tripDayId,
                $seqNo
            );
        });
    }

    /**
     * 7. ScheduleItem 재배치
     * - 같은 TripDay 내에서만 이동 가능
     * @param Trip $trip
     * @param int $dayNo
     * @param int $oldSeqNo // 기존 순번
     * @param int $newSeqNo // 새로운 순번
     * @return void
     * @throws ModelNotFoundException
     */
    public function reorderScheduleItem(
        Trip $trip,
        int $dayNo,
        int $itemId,
        int $newSeqNo
    ): void {
        // tripDayId 조회
        $tripDayId = $this->getTripDayIdOrFail($trip, $dayNo);

        DB::transaction(function () use (
            $tripDayId,
            $itemId,
            $newSeqNo
        ) {
            // ScheduleItem 이동 대상 조회
            $item = $this->scheduleItemRepository->findById($itemId);

            // 없으면 예외 발생
            if (is_null($item)) {
                throw new ModelNotFoundException("해당하는 Schedule Item을 찾을 수 없습니다");
            }

            // newSeqNo 보정
            $maxSeqNo = $this->scheduleItemRepository->getMaxSeqNo($tripDayId);
            
            if ($maxSeqNo === 0) {
                throw new ModelNotFoundException("재배치할 Schedule Item이 없습니다");
            }

            if ($newSeqNo < 1) {
                $newSeqNo = 1;
            }
            else if ($newSeqNo > $maxSeqNo) {
                $newSeqNo = $maxSeqNo;
            }

            if ($itemId === $newSeqNo) {
                // 이동 없음
                return;
            }

            if ($itemId < $newSeqNo) {
                // 앞으로 이동: oldSeqNo+1 ~ newSeqNo 항목들 seq_no 1씩 감소
                $this->scheduleItemRepository->decrementSeqNos(
                    $tripDayId,
                    $itemId
                );
            } else {
                // 뒤로 이동: newSeqNo ~ oldSeqNo-1 항목들 seq_no 1씩 증가
                $this->scheduleItemRepository->incrementSeqNos(
                    $tripDayId,
                    $newSeqNo
                );
            }

            // 대상 항목의 seq_no 업데이트
            $this->scheduleItemRepository->updateSeqNo(
                $tripDayId,
                $itemId,
                $newSeqNo
            );
        });
    }

}