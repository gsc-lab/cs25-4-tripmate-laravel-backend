<?php
namespace App\Services\Trip;

use App\Models\ScheduleItem;
use App\Models\Trip;
use App\Repositories\Trip\ScheduleItemRepository;
use App\Repositories\Trip\TripDayRepository;
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
        TripDayRepository $tripDayRepository
    ) {
        $this->scheduleItemRepository = $scheduleItemRepository;
        $this->tripDayRepository = $tripDayRepository;
    }

    /**
     * 내부 공통 헬퍼 메서드
     * - Trip + day_no로 Trip_day_id 조회
     * - 없으면 ModelNotFoundException 예외 발생
     * @throws ModelNotFoundException
     */
    protected function getTripDayIdOrFail(
        Trip $trip,
        int $tripDayId
    ): int {

        // trip_day_id가 해당 trip에 속하는지 검증
        $count = $this->tripDayRepository->countByTripAndTripDayIds(
            $trip->trip_id,
            [$tripDayId]
        );

        if (! $count) {
            throw new ModelNotFoundException('해당하는 Trip Day를 찾을 수 없습니다');
        }

        return $tripDayId;
        }

    /**
     * 내부 공통 헬퍼 메서드
     * - schedule_item_id로 ScheduleItem 조회
     * - Trip + day_no에 속하는지까지 확인
     *
     * @throws ModelNotFoundException
     */
    protected function getOwnedScheduleItemOrFail(Trip $trip, int $tripDayId, int $itemId): ScheduleItem
    {
        // Trip_day_id 조회
        $tripDayId = $this->getTripDayIdOrFail($trip, $tripDayId);

        /** @var ScheduleItem|null $item */
        $item = $this->scheduleItemRepository->findById($itemId);

        if ($item === null || (int) $item->trip_day_id !== (int) $tripDayId) {
            throw new ModelNotFoundException('해당하는 Schedule Item을 찾을 수 없습니다');
        }

        return $item;
    }

    /**
     * 1. 특정 TripDay의 ScheduleItem 목록 조회 (페이지네이션)
     * @throws ModelNotFoundException
     */
    public function paginateScheduleItems(
        Trip $trip,
        int $tripDayId,
        array $pagination
    ): LengthAwarePaginator {
        $resolvedTripDayId = $this->getTripDayIdOrFail($trip, $tripDayId);

        return $this->scheduleItemRepository->paginateByTripDayId(
            $resolvedTripDayId,
            $pagination['page'],
            $pagination['size']
        );
    }

    /**
     * 2. ScheduleItem 생성
     * - seq_no가 null이면 해당 TripDay의 마지막 seq_no + 1로 설정
     * - seq_no가 있으면 해당 seq_no 이후의 항목들의 seq_no 1씩 증가
     * @throws ModelNotFoundException
     */
    public function createScheduleItem(
        Trip $trip,
        int $tripDayId,
        array $payload
    ): ScheduleItem {
        // Trip 소유 + TripDay 소속 검증 (tripDayId를 받았으면 dayNo로 재조회하면 안 됨)
        $tripDayId = $this->getTripDayIdOrFail($trip, $tripDayId);
    
        return DB::transaction(function () use ($tripDayId, $payload) {
            // seq_no 결정
            $seqNo = $payload['seq_no'] ?? null;
    
            if (is_null($seqNo)) {
                // 마지막 seq_no + 1 (비어있으면 1부터)
                $maxSeqNo = $this->scheduleItemRepository->getMaxSeqNo($tripDayId); // int|null
                $seqNo = ($maxSeqNo ?? 0) + 1;
            } else {
                // 해당 seq_no부터 뒤로 밀기 (삽입 자리를 비움)
                $this->scheduleItemRepository->incrementSeqNos($tripDayId, (int) $seqNo);
            }
    
            return $this->scheduleItemRepository->create([
                'trip_day_id' => $tripDayId,
                'place_id'    => $payload['place_id'],
                'seq_no'      => $seqNo,
                'visit_time'  => $payload['visit_time'] ?? null,
                'memo'        => $payload['memo'] ?? null,
            ]);
        });
    }

    /**
     * 3. ScheduleItem 단건 조회
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
     * 4. ScheduleItem 메모/방문시간 수정
     * - payload에 포함된 필드만 업데이트
     * @param array{visit_time?: ?string, memo?: ?string} $payload
     */
    public function updateScheduleItem(
        Trip $trip,
        int $tripDayId,
        int $itemId,
        array $payload
    ): ScheduleItem {
        // tripDayId 조회
        $item = $this->getOwnedScheduleItemOrFail($trip, $tripDayId, $itemId);

        // payload에 포함된 필드만 업데이트
        if (array_key_exists('visit_time', $payload)) {
            $item->visit_time = $payload['visit_time']; 
        }
    
        if (array_key_exists('memo', $payload)) {
            $item->memo = $payload['memo']; 
        }

        $item->save();
    
        return $item;
    }

    /**
     * 5. ScheduleItem 삭제
     * - 해당 item 삭제 후 같은 TripDay 내 seq_no 연속성 유지
     * @throws ModelNotFoundException
     */
    public function deleteScheduleItem(
        Trip $trip,
        int $tripDayId,
        int $itemId
    ): void {
        // item + TripDay 소속 검증
        $item = $this->getOwnedScheduleItemOrFail($trip, $tripDayId, $itemId);

        // tripDayId, deletedSeqNo 확인
        $tripDayId = (int) $item->trip_day_id;
        $deletedSeqNo = (int) $item->seq_no;

        // 트랜잭션으로 삭제 및 뒤 항목 시프트
        DB::transaction(function () use ($item, $tripDayId, $deletedSeqNo) {
            // item 삭제
            $item->delete();

            // 뒤 항목들 시프트
            $this->scheduleItemRepository->decrementSeqNos($tripDayId, $deletedSeqNo);
        });
    }

    /**
     * 7. ScheduleItem 재배치
     * - 다중/단일 재비치 지원
     * - 같은 Trip 내 TripDay 간 이동 지원
     * @param array<int, array{trip_day_id:int, item_ids: array<int, int>}> $orders
     */
    public function reorderScheduleItems(Trip $trip, array $orders): void
    {
        if (empty($orders)) {
            return;
        }

        DB::transaction(function () use ($trip, $orders) {

            $targetTripDayIds = [];
            $allItemIds = [];

            // 유효성 검사 및 itemId 수집
            foreach ($orders as $order) {
                $tripDayId = (int) ($order['trip_day_id'] ?? 0);
                $itemIds = array_values($order['item_ids'] ?? []);

                if ($tripDayId <= 0) {
                    throw new ModelNotFoundException('유효하지 않은 trip_day_id 값입니다.');
                }

                if (empty($itemIds)) {
                    continue;
                }

                // 이 tripDayId 가 실제로 이 Trip 에 속하는지 검증
                $count = $this->tripDayRepository->countByTripAndTripDayIds(
                    $trip->trip_id,
                    [$tripDayId]
                );

                if (! $count) {
                    throw new ModelNotFoundException("trip_day_id {$tripDayId} 가 이 Trip 에 속하지 않습니다.");
                }

                $targetTripDayIds[] = $tripDayId;
                $allItemIds = array_merge($allItemIds, $itemIds);
            }

            if (empty($allItemIds)) {
                return;
            }

            if (count($allItemIds) !== count(array_unique($allItemIds))) {
                throw new \DomainException('중복된 schedule_item_id 가 포함되어 있습니다.');
            }

            $uniqueItemIds = array_values(array_unique($allItemIds));

            // 아이템 존재 여부 확인
            $items = $this->scheduleItemRepository->getByItemIds($uniqueItemIds);
            if ($items->count() !== count($uniqueItemIds)) {
                throw new ModelNotFoundException('일부 ScheduleItem 을 찾을 수 없습니다.');
            }

            // 같은 Trip 소속인지 검증 + 원래 trip_day_id 수집
            $originalTripDayIds = [];

            foreach ($items as $item) {
                $tripDay = $item->tripDay;
                $itemTrip = $tripDay?->trip;

                if (! $tripDay || ! $itemTrip) {
                    throw new ModelNotFoundException('일부 ScheduleItem 의 Trip/TripDay 정보를 확인할 수 없습니다.');
                }

                if ((int) $itemTrip->trip_id !== (int) $trip->trip_id) {
                    throw new \DomainException('다른 Trip 에 속한 ScheduleItem 은 재배치할 수 없습니다.');
                }

                $originalTripDayIds[] = (int) $item->trip_day_id;
            }

            // 재배치 대상 TripDay ID 목록
            $affectedTripDayIds = array_values(array_unique(array_merge(
                array_values(array_unique($originalTripDayIds)),
                array_values(array_unique($targetTripDayIds)),
            )));

            if (empty($affectedTripDayIds)) {
                return;
            }

            // 충돌 방지: seq_no 전체 임시 이동
            $this->scheduleItemRepository->tempShiftSeqNos($affectedTripDayIds, 1000);

            // orders 반영
            foreach ($orders as $order) {
                $tripDayId = (int) ($order['trip_day_id'] ?? 0);
                $itemIds = array_values($order['item_ids'] ?? []);

                if ($tripDayId <= 0 || empty($itemIds)) {
                    continue;
                }

                $this->scheduleItemRepository->reorderSeqNosByItemIds($tripDayId, $itemIds);
            }

            // 영향 TripDay seq_no normalize
            foreach ($affectedTripDayIds as $tripDayId) {
                $this->scheduleItemRepository->normalizeSeqNosForTripDay($tripDayId);
            }
        });
    }

    /**
     * 7. getlatlngBy Repository
     * - dayNo로 조회하여 latlng 반환
     * @return array<int, array{lat: float, lng: float}>
     */
    public function getlatlng($trip, $tripDayId)
    {
        // Trip_day_id 조회
        $tripDayId = $this->getTripDayIdOrFail($trip, $tripDayId);

        return $this->scheduleItemRepository->getlatlngFromPlaceId($tripDayId);
    }

    /**
     * 8. 거리 계산 헬퍼 메서드
     * @param array<int, array{lat: float, lng: float}> $latlng
     * @return array{segments: array<int, array{from_index:int,to_index:int,distance_km: float}>, total_km: float}
     */
    public function calculateRouteDistances(array $latlng)
    {
        $distance = [];
        $totalDistance = 0.0;

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
     * 9. 장소 간 거리 계산 서비스
     * @return array{segments: array<int, array{from_index:int,to_index:int,distance_km: float}>, total_km: float}
     */
    public function calculateRouteDistancesByDistance($trip, $tripDayId)
    {
        // place의 좌표 조회
        $latlng = $this->getlatlng($trip, $tripDayId);

        // 좌표 계산
        return $this->calculateRouteDistances($latlng);
    }
}