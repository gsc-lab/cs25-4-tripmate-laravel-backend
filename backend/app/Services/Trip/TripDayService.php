<?php

namespace App\Services\Trip;

use App\Models\Trip;
use App\Models\TripDay;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Repositories\Trip\TripDayRepository;
use App\Repositories\Trip\ScheduleItemRepository;
use Illuminate\Support\Facades\DB;

/**
 * TripDayService 
 * - TripDay 생성/수정/삭제/조회/재정렬
 * - day_count 보정
 */
class TripDayService 
{   
    // repository 프로퍼티
    protected TripDayRepository $tripDayRepository;
    protected ScheduleItemRepository $scheduleItemRepository;

    // 생성자에서 repository 주입
    public function __construct(
        TripDayRepository $tripDayRepository,
        ScheduleItemRepository $scheduleItemRepository
        ) {
            $this->tripDayRepository = $tripDayRepository;
            $this->scheduleItemRepository = $scheduleItemRepository;
        }
    
    
    /**
     * 1. 특정 Trip의 TripDay 목록 조회 (페이지네이션)
     * @param Trip $trip
     * @param int $page
     * @param int $size
     */
    public function paginateByTripDays(
        Trip $trip,
        int $page,
        int $size
    ){
        return $this->tripDayRepository->paginateByTripDay(
            $trip->trip_id,
            $page,
            $size
        );
    }

    /**
     * 2. TripDay 생성 
     * - 중간 삽입 포함
     * @param Trip $trip
     * @param int $dayNo
     * @param string|null $memo
     * @return TripDay
     */
    public function createTripDay(
        Trip $trip,
        int $dayNo,
        ?string $memo = null
        ): TripDay {

            $tripId = $trip->trip_id;

            return DB::transaction(function () use ($tripId, $dayNo, $memo) {
                
            // 중간 삽입인 경우 day_no 이후의 day_no 들을 1씩 증가
            if ($this->tripDayRepository->existDayNo($tripId, $dayNo)) {
                    $this->tripDayRepository->incrementDayNo($tripId, $dayNo);
            }

            // TripDay 생성
            $day = $this->tripDayRepository->create([
                'trip_id' => $tripId,
                'day_no' => $dayNo,
                'memo' => $memo
            ]);

                return $day;
            });
        }
    
    /**
     * 3. TripDay 메모 수정
     * @param Trip $trip
     * @param int $dayNo
     * @param string|null $memo
    */
    public function updateTripDayMemo(
        Trip $trip,
        int $dayNo,
        ?string $memo = null
    ): void {
        $this->tripDayRepository->updateMemo(
            $trip->trip_id,
            $dayNo,
            $memo
        );
    }

    /**
     * 4. TripDay 단건 조회
     * @param Trip $trip
     * @param int $dayNo
     * @return TripDay
     * @throws ModelNotFoundException
     */
    public function getTripDay(
        Trip $trip,
        int $dayNo
    ): TripDay {
        $row = $this->tripDayRepository->findByTripAndDayNo(
            $trip->trip_id,
            $dayNo
        );

        if (!$row){
            throw new ModelNotFoundException('해당 일차가 존재하지 않습니다');
        }

        return $row;
    }

    /**
     * 5. TripDay 삭제
     * - day_count 보정
     * - 연관된 ScheduleItem 삭제
     * @param Trip $trip
     * @param int $dayNo
     * @return void
     * @throws ModelNotFoundException
     */
    public function deleteTripDay(
        Trip $trip,
        int $dayNo
        ): void {
            
            $tripId = $trip->trip_id;

            DB::transaction(function () use ($tripId, $dayNo) {
                
                // tripday 단건 조회
                $day = $this->tripDayRepository->findByTripAndDayNo(
                    $tripId,
                    $dayNo
                );

                // 존재하지 않으면 예외 발생
                if (!$day){
                    throw new ModelNotFoundException('삭제 할 일차가 존재하지 않습니다');
                }

                // 삭제 (TripDay FK로 ScheduleItem은 cascade 삭제)
                $day->delete();

                // day_no 이후의 day_no 들을 1씩 감소
                $this->tripDayRepository->decrementDayNoAfter($tripId, $dayNo);

            });
        }

    /**
     * 6. TripDay 번호 재정렬
     * - day_no가 중간에 비는 경우 연속성 유지를 위해 재정렬
     * @param Trip $trip
     * @param int $oldDayNo
     * @param int $newDayNo
     * @return void
     * @throws ModelNotFoundException
     */
    public function reorderTripDay(
        Trip $trip,
        int $oldDayNo,
        int $newDayNo
    ): void {

        $tripId = $trip->trip_id;

        DB::transaction(function () use ($tripId, $oldDayNo, $newDayNo) {
            
            if ($oldDayNo === $newDayNo) {
                // 변경 사항이 없으면 아무 작업도 하지 않음
                return;
            }

            // 변경 전 일차 조회
            $day = $this->tripDayRepository->findByTripAndDayNo(
                $tripId,
                $oldDayNo
            );

            // 존재하지 않으면 예외 발생
            if (!$day){
                throw new ModelNotFoundException('변경 할 일차가 존재하지 않습니다');
            }

            // 임시 day_no로 이동
            $maxDayNo = $this->tripDayRepository->getMaxDayNo($tripId);
            $tempDayNo = $maxDayNo + 1000;

            // 임시 번호로 변경
            $this->tripDayRepository->updateDayNo(
                $tripId,
                $oldDayNo,
                $tempDayNo
            );

            // 중간 구간 이동
            if ($oldDayNo < $newDayNo) {
                // 아래로 이동 : oldDayNo < day_no <= newDayNo  인 day_no 들을 -1 씩 감소
                $this->tripDayRepository->shiftDownRange(
                    $tripId,
                    $oldDayNo,
                    $newDayNo
                );
            } else {
                // 위로 이동 : newDayNo <= day_no < oldDayNo 인 day_no 들을 +1 씩 증가
                $this->tripDayRepository->shiftUpRange(
                    $tripId,
                    $oldDayNo,
                    $newDayNo
                );
            }

            // 임시 번호를 최종 번호로 변경
            $this->tripDayRepository->updateDayNo(
                $tripId,
                $tempDayNo,
                $newDayNo
            );
        });
    }   
}