<?php
namespace App\Services\Trip;

use App\Models\Trip;
use App\Models\TripDay;
use App\Repositories\Trip\TripDayRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * TripDayService
 * - TripDay 생성/수정/삭제/조회/재정렬
 * - day_count 보정
 */
class TripDayService
{
    // repository 프로퍼티
    protected TripDayRepository $tripDayRepository;

    // 생성자에서 repository 주입
    public function __construct(TripDayRepository $tripDayRepository) 
    {
        $this->tripDayRepository = $tripDayRepository;
    }

    /**
     * 1. 특정 Trip의 TripDay 목록 조회 (페이지네이션)
     */
    public function paginate(
        Trip $trip,
        int $page,
        int $size
    ) {
        return $this->tripDayRepository->paginateByTripId(
            $trip->trip_id,
            $page,
            $size
        );
    }

    /**
     * 2. TripDay 생성
     * - 중간 삽입 포함
     */
    public function store(
        Trip $trip,
        int $dayNo,
        ?string $memo = null
    ): TripDay {
        $tripId = $trip->trip_id;

        return DB::transaction(function () use ($tripId, $dayNo, $memo) {

            // 중간 삽입인 경우 day_no 이후의 day_no 들을 1씩 증가
            if ($this->tripDayRepository->existDayNo($tripId, $dayNo)) {
                $this->tripDayRepository->incrementDayNoFrom($tripId, $dayNo);
            }

            /** @var TripDay $day */
            $day = $this->tripDayRepository->create([
                'trip_id' => $tripId,
                'day_no' => $dayNo,
                'memo' => $memo,
            ]);

            return $day;
        });
    }

    /**
     * 3. TripDay 메모 수정
     * @throws ModelNotFoundException
     */
    public function update(
        Trip $trip,
        int $dayNo,
        ?string $memo = null
    ): void {
        $this->show($trip, $dayNo);

        $this->tripDayRepository->updateMemoByTripIdAndDayNo(
            $trip->trip_id,
            $dayNo,
            $memo
        );
    }

    /**
     * 4. TripDay 단건 조회
     *
     * @throws ModelNotFoundException
     */
    public function show(
        Trip $trip,
        int $dayNo
    ): TripDay {
        $day = $this->tripDayRepository->findByTripIdAndDayNo(
            $trip->trip_id,
            $dayNo
        );

        if (! $day) {
            throw new ModelNotFoundException('해당 일차가 존재하지 않습니다');
        }

        return $day;
    }

    /**
     * 5. TripDay 삭제
     * - day_no 이후의 day_no 들을 1씩 감소
     * @throws ModelNotFoundException
     */
    public function destroy(
        Trip $trip,
        int $dayNo
    ): void {
        $tripId = $trip->trip_id;

        DB::transaction(function () use ($tripId, $dayNo) {
            $day = $this->tripDayRepository->findByTripIdAndDayNo(
                $tripId,
                $dayNo
            );

            if (! $day) {
                throw new ModelNotFoundException('삭제 할 일차가 존재하지 않습니다');
            }

            // 삭제 (TripDay FK로 ScheduleItem은 cascade 삭제)
            $day->delete();

            // day_no 이후의 day_no 들을 1씩 감소
            $this->tripDayRepository->decrementDayNoAfter($tripId, $dayNo);
        });
    }

    /**
     * 6. tripDay 전체 재배치
     * - 프론트에서 전발답은 최종 순서 기준으로 재배치
     * - 임시로 큰 번호를 부여한 후 최종 번호로 변경
     * @param  int[]  $dayIds
     * @throws ModelNotFoundException
     * @throws \InvalidArgumentException
     */
    public function reorder(
        Trip $trip,
        array $dayIds
    ): void {
        $tripId = $trip->trip_id;

        DB::transaction(function () use ($tripId, $dayIds) {

            // 값이 비어있는지 확인
            if (empty($dayIds)) {
                throw new \InvalidArgumentException('dayIds 배열이 비어있습니다');
            }

            // trip의 전체 일차 개수와 dayIds 개수가 일치하는지 확인
            $dayCount = $this->tripDayRepository->countByTripId($tripId);
            if (count($dayIds) !== $dayCount) {
                throw new \InvalidArgumentException(
                    'dayIds 배열의 개수가 일치하지 않습니다'
                );
            }

            // 중복 된 dayIds가 있는지 확인
            if (count($dayIds) !== count(array_unique($dayIds))) {
                throw new \InvalidArgumentException(
                    'dayIds 배열에 중복 된 값이 있습니다'
                );
            }

            // 모든 dayIds가 해당 trip에 속하는지 확인
            $foundCount = $this->tripDayRepository->countByTripAndTripDayIds(
                $tripId,
                $dayIds
            );
            if ($foundCount !== count($dayIds)) {
                throw new ModelNotFoundException(
                    '일부 일차가 해당 여행에 속하지 않습니다'
                );
            }

            // 임시로 큰 번호 부여
            $this->tripDayRepository->tempShiftDayNo($tripId, 1000);

            // 최종 번호로 변경
            $newDayNo = 1;
            foreach ($dayIds as $dayId) {
                $this->tripDayRepository->updateDayNoByTripDayId(
                    $tripId,
                    $dayId,
                    $newDayNo
                );

                $newDayNo++;
            }
        });
    }
}