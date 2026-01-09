<?php

namespace App\Services\Trip;

use App\Models\Trip;
use App\Repositories\Trip\TripDayRepository;
use App\Repositories\Trip\TripRepository;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TripService
{
    // trip, tripday repository 프로퍼티
    protected TripRepository $tripRepository;

    protected TripDayRepository $tripDayRepository;

    /**
     * 생성자에서 repository 주입
     */
    public function __construct(
        TripRepository $tripRepository,
        TripDayRepository $tripDayRepository)
    {
        $this->tripRepository = $tripRepository;
        $this->tripDayRepository = $tripDayRepository;
    }

    /**
     * 내부 공통 메서드
     * - Trip이 현재 로그인한 사용자 소유인지 확인
     * - 소유자가 아니면 AuthorizationException 예외 발생
     *
     * @throws AuthorizationException
     */
    protected function assertTripOwnership(Trip $trip): void
    {
        // 현재 로그인한 사용자 ID 가져오기
        $authUserId = Auth::id();

        // 로그인 안되었거나 소유자가 다르면 예외 발생
        if ($authUserId === null || $trip->user_id !== $authUserId) {
            throw new AuthorizationException('본인 소유의 여행이 아닙니다');
        }
    }

    /**
     * 내부 공통 메서드
     * - trip_id로 Trip 조회 후 현재 로그인한 사용자의 소유인지 확인
     *  Trip/TripDay/ScheduleItem 컨트롤러에서 공통 사용
     * @throws AuthorizationException
     */
    public function getOwnedTripOrFail(int $tripId): Trip
    {
        // trip_id로 Trip 조회
        $trip = $this->tripRepository->findTripOrFail($tripId);

        // 소유자 확인
        $this->assertTripOwnership($trip);

        return $trip;
    }

    /**
     * 1. Trip 생성
     * - tripday 자동 생성
     * @param array{title:string, region_id:int, start_date:string, end_date:string} $payload
     */
    public function store(array $payload): Trip
    {
        // 현재 로그인한 사용자 ID 가져오기
        $userId = Auth::id();
        if ($userId === null) {
            throw new AuthorizationException('로그인한 사용자만 여행을 생성할 수 있습니다');
        }

        // payload에 user_id 추가
        $payload['user_id'] = $userId;

        // trip 생성
        return DB::transaction(function () use ($payload) {
            // Trip 생성
            $trip = $this->tripRepository->createTrip($payload);

            // 시작일과 종료일 파싱
            $startDate = Carbon::parse($trip->start_date);
            $endDate = Carbon::parse($trip->end_date);

            // TripDay 생성
            $dayNo = 1;

            // start_date ~ end_date 까지 하루씩 증가
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay(), $dayNo++) {
                // TripDay 생성
                $this->tripDayRepository->create([
                    'trip_id' => $trip->trip_id,
                    'day_no' => $dayNo,
                    'memo' => null,
                ]);
            }

            // 생성된 Trip 반환
            return $trip->fresh();
        });
    }

    /**
     * 2. Trip 목록 조회 (페이지네이션)
     */
    public function paginate(
        int $page,
        int $size,
        ?string $sort = null,
        ?int $regionId = null
    ): LengthAwarePaginator {

        // 현재 로그인한 사용자 ID 가져오기
        $userId = Auth::id();
        if ($userId === null) {
            throw new AuthorizationException('로그인이 필요합니다');
        }

        // Trip 목록 페이지네이션 조회
        return $this->tripRepository->paginateTrips(
            $userId,
            $page,
            $size,
            $sort,
            $regionId
        );
    }

    /**
     * 3. 단일 Trip 조회
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(int $tripId): Trip
    {
        // 소유자 확인 및 Trip 조회
        return $this->getOwnedTripOrFail($tripId);
    }

    /**
     * 4. Trip 부분 업데이트
     * @param array{title?:string, region_id?:int, start_date?:string, end_date?:string} $payload
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(
        int $tripId,
        array $payload
    ): Trip {
        // 소유자 확인 및 Trip 조회
        $this->getOwnedTripOrFail($tripId);

        // Trip 부분 업데이트
        $updatedTrip = $this->tripRepository->updateTrip(
            $tripId,
            $payload
        );

        // 업데이트된 Trip 반환
        return $updatedTrip;
    }

    /**
     * 5. Trip 삭제
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(int $tripId): bool
    {
        // 소유자 확인 및 Trip 조회
        $this->getOwnedTripOrFail($tripId);

        // Trip 삭제
        return $this->tripRepository->deleteTrip($tripId);
    }
}