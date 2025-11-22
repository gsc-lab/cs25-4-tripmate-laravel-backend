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
            if (!$tripDayId) {
                throw new ModelNotFoundException("해당하는 Trip Day를 찾을 수 없습니다");
            }

            // Trip_day_id 반환
            return $tripDayId;
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

    // /**
    //  * 3. ScheduleItem 생성
    //  * - seq_no가 null이면 해당 TripDay의 마지막 seq_no + 1로 설정
    //  * - seq_no가 있으면 해당 seq_no 이후의 항목들의 seq_no 1씩 증가
    //  * @param Trip $trip
    //  * @param int $dayNo
    //  * @param int|null $seqNo
    //  * @param int|null $placeId 
    //  * @param string|null $visitTime
    //  * @param string|null $memo
    //  * @return ScheduleItem
    //  * @throws ModelNotFoundException
    //  */
    // public function createScheduleItem(
    //     Trip $trip, 
    //     int $dayNo,
    //     ?int $seqNo,
    //     ?int $placeId,
    //     ?string $visitTime,
    //     ?string $memo
    //     ): ScheduleItem {
    //         // Trip_day_id 조회
    //         $tripDayId = $this->getTripDayIdOrFail($trip, $dayNo);

    //         return DB::transaction(function () use (
    //             $tripDayId,
    //             $seqNo,
    //             $placeId,
    //             $visitTime,
    //             $memo
    //         ) {

    //             // 현재 최대 seq_no 조회
    //             $maxSeqNo = $this->scheduleItemRepository->getMaxSeqNo($tripDayId);

    //             // seq_no가 null이면 마지막 seq_no + 1로 설정
    //             if (is_null($seqNo)) {
    //                 $seqNo = $maxSeqNo + 1;
    //             } else {
    //                 // 최소값 보정 (1보다 작으면 1로 설정)
    //                 if ($seqNo < 1) {
    //                     $seqNo = 1;
    //                 }

    //                 // 최대값 보정 (현재 maxSeqNo + 1 보다 크면 맨 뒤)
    //                 if ($seqNo > $maxSeqNo + 1) {
    //                     $seqNo = $maxSeqNo + 1;
    //                 }

    //                 // 이미 해당 seq_no가 존재하면 이후 항목들의 seq_no 1씩 증가
    //                 if ($$this->scheduleItemRepository->existsSeqNo(
    //                     $tripDayId,
    //                     $seqNo
    //                 )) {
    //                     $this->scheduleItemRepository->incrementSeqNos(
    //                         $tripDayId,
    //                         $seqNo
    //                     );
    //                 }
    //             }

    //             // ScheduleItem 생성
    //             return $this->scheduleItemRepository->createScheduleItem(
    //                 $tripDayId,
    //                 $seqNo,

    // }
}