<?php

namespace App\Services\Trip;

use App\Models\ScheduleItem;
use App\Models\Trip;
use App\Models\TripDay;
use App\Repositories\Trip\ScheduleItemRepository;
use App\Repositories\Trip\TripDayRepository;
use Carbon\Carbon;
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
     *
     * @throws ModelNotFoundException
     */
    protected function getTripDayIdOrFail(
        Trip $trip,
        int $tripDayId
    ): int {

        // Trip_day_id 조회
        $tripDayId = $this->tripDayRepository->getTripDayId(
            $trip->trip_id,
            $tripDayId
        );

        // 없으면 예외 발생
        if (is_null($tripDayId)) {
            throw new ModelNotFoundException('해당하는 Trip Day를 찾을 수 없습니다');
        }

        // Trip_day_id 반환
        return $tripDayId;
    }

    /**
     * 내부 공통 헬퍼 메서드
     * - schedule_item_id로 ScheduleItem 조회
     * - Trip + day_no에 속하는지까지 확인
     *
     * @param  int  $itemId  schedule_item_id
     *
     * @throws ModelNotFoundException
     */
    protected function getOwnedScheduleItemOrFail(
        Trip $trip,
        int $tripDayId,
        int $itemId
    ): ScheduleItem {
        // TripDay 존재 여부 및 trip_day_id 조회
        $tripDayId = $this->getTripDayIdOrFail($trip, $tripDayId);

        // PK(scheduled_item_id)로 조회
        /** @var ScheduleItem|null $item */
        $item = $this->scheduleItemRepository->findById($itemId);

        // 없거나 다른 TripDay에 속하면 예외
        if (! $item || $item->trip_day_id !== $tripDayId) {
            throw new ModelNotFoundException('해당하는 Schedule Item을 찾을 수 없습니다');
        }

        return $item;
    }

    /**
     * 1. 특정 TripDay의 ScheduleItem 목록 조회 (페이지네이션)
     *
     * @throws ModelNotFoundException
     */
    public function paginateScheduleItems(
        Trip $trip,
        int $tripDayId,
        int $page,
        int $size
    ): LengthAwarePaginator {
        // Trip_day_id 조회
        $tripDayId = $this->getTripDayIdOrFail($trip, $tripDayId);

        // ScheduleItem 목록 페이지네이션 조회
        return $this->scheduleItemRepository->paginateSchedulers(
            $tripDayId,
            $page,
            $size
        );
    }

    /**
     * 2. 특정 TripDay의 ScheduleItem 전체 목록 조회 (페이지네이션 없음)
     *
     * @throws ModelNotFoundException
     */
    public function listScheduleItems(
        Trip $trip,
        int $tripDayId
    ): Collection {
        // Trip_day_id 조회
        $tripDayId = $this->getTripDayIdOrFail($trip, $tripDayId);

        // ScheduleItem 전체 목록 조회
        return $this->scheduleItemRepository->getByTripDayId(
            $tripDayId
        );
    }

    /**
     * 3. ScheduleItem 생성
     * - seq_no가 null이면 해당 TripDay의 마지막 seq_no + 1로 설정
     * - seq_no가 있으면 해당 seq_no 이후의 항목들의 seq_no 1씩 증가
     *
     * @throws ModelNotFoundException
     */
    public function createScheduleItem(
        Trip $trip,
        int $tripDayId,
        ?int $seqNo,
        ?int $placeId,
        ?string $visitTime,
        ?string $memo
    ): ScheduleItem {
        // Trip_day_id 조회
        $tripDayId = $this->getTripDayIdOrFail($trip, $tripDayId);

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
                'memo' => $memo,
            ]);

            return $item;
        });

    }

    /**
     * 4. ScheduleItem 단건 조회
     *
     * @param  int  $itemId  schedule_item_id
     *
     * @throws ModelNotFoundException
     */
    public function getScheduleItem(
        Trip $trip,
        int $tripDayId,
        int $itemId
    ): ScheduleItem {
        return $this->getOwnedScheduleItemOrFail($trip, $tripDayId, $itemId);
    }

    /**
     * 5. ScheduleItem 메모/방문시간 수정
     * = 둘 중 일부만 수정 가능
     *
     * @throws ModelNotFoundException
     */
    public function updateScheduleItem(
        Trip $trip,
        int $tripDayId,
        int $itemId,
        ?string $visitTime,
        ?string $memo
    ): ScheduleItem {
        // tripDayId 조회
        $item = $this->getOwnedScheduleItemOrFail($trip, $tripDayId, $itemId);

        // 메모/방문시간 수정
        if (! is_null($visitTime)) {
            $item->visit_time = Carbon::parse($visitTime);
        }
        if (! is_null($memo)) {
            $item->memo = $memo;
        }

        $item->save();

        return $item;
    }

    /**
     * 6. ScheduleItem 삭제
     * - 이후 항목들의 seq_no 1씩 감소
     *
     * @throws ModelNotFoundException
     */
    public function deleteScheduleItem(
        Trip $trip,
        int $tripDayId,
        int $seqNo
    ): void {
        // tripDayId 조회
        $tripDayId = $this->getTripDayIdOrFail($trip, $tripDayId);

        DB::transaction(function () use ($tripDayId, $seqNo) {

            // ScheduleItem 삭제 대상 조회
            $item = $this->scheduleItemRepository->findByTripDayIdAndSeqNo(
                $tripDayId,
                $seqNo
            );

            // 없으면 예외 발생
            if (is_null($item)) {
                throw new ModelNotFoundException('해당하는 Schedule Item을 찾을 수 없습니다');
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

    // /**
    //  * 7. ScheduleItem 재배치
    //  * - 같은 TripDay 내에서 seq_no 연속성을 유지하며 이동
    //  *
    //  * @param Trip $trip              대상 Trip (소유권 이미 검증됨)
    //  * @param int  $tripDayId             TripDay 번호
    //  * @param int  $itemId            이동할 ScheduleItem ID
    //  * @param int  $newSeqNo          새로운 순번
    //  *
    //  * @return void
    //  * @throws ModelNotFoundException
    //  */
    // public function reorderScheduleItem(
    //     Trip $trip,
    //     int $tripDayId,
    //     int $itemId,
    //     int $newSeqNo
    // ): void {

    //     // 아이템 + TripDay 소속 검증
    //     $item = $this->getOwnedScheduleItemOrFail($trip, $tripDayId, $itemId);
    //     $tripDayId = $item->trip_day_id;
    //     $oldSeqNo  = $item->seq_no;

    //     DB::transaction(function () use ($tripDayId, $oldSeqNo, $newSeqNo) {

    //         // 최대 seq_no 조회
    //         $maxSeqNo = $this->scheduleItemRepository->getMaxSeqNo($tripDayId);

    //         if ($maxSeqNo === 0) {
    //             throw new ModelNotFoundException('재배치할 Schedule Item을 찾을 수 없습니다');
    //         }

    //         // newSeqNo 보정
    //         if ($newSeqNo < 1) {
    //             $newSeqNo = 1;
    //         } elseif ($newSeqNo > $maxSeqNo) {
    //             $newSeqNo = $maxSeqNo;
    //         }

    //         // 이동할 위치가 동일하면 아무 작업도 하지 않음
    //         if ($oldSeqNo === $newSeqNo) {
    //             return;
    //         }

    //         // 임시 seq_no (충돌 방지용)
    //         $tempSeqNo = $maxSeqNo + 1000;

    //         // 임시 번호로 변경
    //         $this->scheduleItemRepository->updateSeqNo(
    //             $tripDayId,
    //             $oldSeqNo,
    //             $tempSeqNo
    //         );

    //         // 중간 구간 shift
    //         if ($oldSeqNo < $newSeqNo) {
    //             // 아래로 이동: oldSeqNo < seq_no <= newSeqNo → -1
    //             $this->scheduleItemRepository->decrementSeqRange(
    //                 $tripDayId,
    //                 $oldSeqNo,
    //                 $newSeqNo
    //             );
    //         } else {
    //             // 위로 이동: newSeqNo <= seq_no < oldSeqNo → +1
    //             $this->scheduleItemRepository->incrementSeqRange(
    //                 $tripDayId,
    //                 $oldSeqNo,
    //                 $newSeqNo
    //             );
    //         }

    //         // 3) 임시 seq_no → 최종 newSeqNo 로 변경
    //         $this->scheduleItemRepository->updateSeqNo(
    //             $tripDayId,
    //             $tempSeqNo,
    //             $newSeqNo
    //         );
    //     });
    // }

    /**
     * 7. ScheduleItem 재배치
     * - 다중 재비치 지원
     * - Cross-Trip 포함
     * - 단일 재배치 포함해서 처리
     */
    public function reorderScheduleItems(Trip $trip, array $orders): void
    {
        if (empty($orders)) {
            return;
        }

        DB::transaction(function () use ($trip, $orders) {

            $targetTripDayIds = [];
            $allItemIds = [];

            // trip_day_id 검증 + 전체 item_id 수집
            foreach ($orders as $order) {
                $tripDayId = (int) ($order['trip_day_id'] ?? 0);
                $itemIds = array_values($order['item_ids'] ?? []);

                if ($tripDayId <= 0) {
                    throw new ModelNotFoundException('유효하지 않은 tripDayId 값입니다.');
                }

                if (empty($itemIds)) {
                    // 이 Day 에 재배치할 항목이 없으면 스킵
                    continue;
                }

                // 이 tripDayId 가 실제로 이 Trip 에 속하는지 검증
                $tripDay = $this->tripDayRepository->countByTripAndTripDayIds(
                    $trip->trip_id,
                    [$tripDayId]
                );

                if (! $tripDay) {
                    throw new ModelNotFoundException("tripDayId {$tripDayId} 가 이 Trip 에 속하지 않습니다.");
                }

                $targetTripDayIds[] = $tripDayId;

                // 전체 item_id 수집 (중복/소속 검증은 아래에서)
                $allItemIds = array_merge($allItemIds, $itemIds);
            }

            if (empty($allItemIds)) {
                return;
            }

            // item_id 중복 체크 (여러 Day 에 같은 Item 이 들어가는 실수 방지)
            if (count($allItemIds) !== count(array_unique($allItemIds))) {
                throw new \DomainException('중복된 schedule_item_id 가 포함되어 있습니다.');
            }

            $uniqueItemIds = array_values(array_unique($allItemIds));

            // 아이템 존재 여부 및 이 Trip 소속인지 검증
            $items = $this->scheduleItemRepository->getByItemIds($uniqueItemIds);

            if ($items->count() !== count($uniqueItemIds)) {
                throw new ModelNotFoundException('일부 ScheduleItem 을 찾을 수 없습니다.');
            }

            $originalTripDayIds = [];

            foreach ($items as $item) {
                $tripDay = $item->tripDay;   // 기존 TripDay
                $itemTrip = $tripDay?->trip;  // 기존 Trip

                if (! $tripDay || ! $itemTrip) {
                    throw new ModelNotFoundException('일부 ScheduleItem 의 Trip/TripDay 정보를 확인할 수 없습니다.');
                }

                // 같은 Trip 안의 TripDay 들만 재배치 허용
                if ($itemTrip->trip_id !== $trip->trip_id) {
                    throw new \DomainException('다른 Trip 에 속한 ScheduleItem 은 재배치할 수 없습니다.');
                }

                $originalTripDayIds[] = $item->trip_day_id;
            }

            $originalTripDayIds = array_values(array_unique($originalTripDayIds));
            $targetTripDayIds = array_values(array_unique($targetTripDayIds));

            // 영향을 받는 TripDay = 원래 Day + 타겟 Day
            $affectedTripDayIds = array_values(array_unique(
                array_merge($originalTripDayIds, $targetTripDayIds)
            ));

            if (empty($affectedTripDayIds)) {
                return;
            }

            // 모든 영향받는 TripDay 의 seq_no 를 임시로 +1000 (충돌 방지)
            $this->scheduleItemRepository->tempShiftSeqNos($affectedTripDayIds, 1000);

            // orders 기준으로 각 Day 의 최종 소속/순서 반영
            foreach ($orders as $order) {
                $tripDayId = (int) ($order['trip_day_id'] ?? 0);
                $itemIds = array_values($order['item_ids'] ?? []);

                if ($tripDayId <= 0 || empty($itemIds)) {
                    continue;
                }

                $this->scheduleItemRepository->reorderSeqNosByItemIds(
                    $tripDayId,
                    $itemIds,
                );
            }

            // 모든 영향받는 TripDay 의 seq_no 재배치
            foreach ($affectedTripDayIds as $tripDayId) {
                $this->scheduleItemRepository->normalizeSeqNosForTripDay($tripDayId);
            }

        });
    }

    /**
     * getlatlngBy Repository
     * - dayid로 조회하여 latlng 반환
     *
     * @param  int  $tripDayId
     * @param  Trip  $trip
     * @return float[]
     */
    public function getlatlng($trip, $tripDayId)
    {
        // Trip_day_id 조회
        $tripDayId = $this->getTripDayIdOrFail($trip, $tripDayId);

        return $this->scheduleItemRepository->getlatlngFromPlaceId($tripDayId);
    }

    /**
     * 거리 계산 헬퍼 메서드
     */
    public function calculateRouteDistances(array $latlng)
    {
        $distance = [];
        $totalDistance = 0;

        // 반복문으로 거리 계산
        for ($i = 1; $i < count($latlng); $i++) {
            $pr = $latlng[$i - 1]; // 출발지
            $cu = $latlng[$i]; // 도착지

            // 거리 계산을 위한 값 전달
            $km = DistanceHelper::calculate(
                $pr['lat'], $pr['lng'],
                $cu['lat'], $cu['lng']
            );

            // 결과 저장
            $distance[] = [
                'from_index' => $i - 1,
                'to_index' => $i,
                'distance_km' => $km,
            ];

            $totalDistance += $km;
        }

        return [
            'segments' => $distance, // 각 거리 결과
            'total_km' => $totalDistance, // 총 소요 거리
        ];
    }

    /**
     * 장소 간 거리 계산 서비스
     *
     * @param  Trip  $trip
     * @param  mixed  $tripDayId
     * @return array{segments: array, total_km: float|int|array{segments: array, total_km: int}}
     */
    public function calculateRouteDistancesByDistance($trip, $tripDayId)
    {
        // place의 좌표 조회
        $latlng = $this->getlatlng($trip, $tripDayId);

        // 좌표 계산
        return $this->calculateRouteDistances($latlng);
    }
}
