<?php

namespace App\Http\Controllers\Trip;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

use App\Http\Requests\TripDay\TripDayIndexRequest;
use App\Http\Requests\TripDay\TripDayStoreRequest;
use App\Http\Requests\TripDay\TripDayUpdateRequest;
use App\Http\Requests\TripDay\TripDayReorderRequest;
use App\Http\Resources\TripDayResource;
use App\Services\Trip\TripService;
use App\Services\Trip\TripDayService;

/**
 * Trip Day Controller
 * - TripDay 조회 / 생성 / 수정 / 삭제 / 재정렬
 */
class TripDayController extends Controller
{
    // services 인스턴스 주입
    protected TripDayService $tripDayService;
    protected TripService $tripService;

    // 생성자 주입
    public function __construct(
        TripDayService $tripDayService,
        TripService $tripService
    ) {
        $this->tripDayService = $tripDayService;
        $this->tripService = $tripService;
    }

    /**
     * 1. TripDay 목록 조회 (페이지네이션)
     * - GET /api/v2/trips/{trip_id}/days
     * @return TripDayIndexRequest $request
     * @param int $tripId
     * @return JsonResponse
     */
    public function index(
        TripDayIndexRequest $request,
        int $tripId
    ): JsonResponse {
        // 현재 로그인 사용자의 Trip인지 확인
        $trip = $this->tripService->getOwnedTripOrFail($tripId);

        // 쿼리 파라미터에서 페이지네이션 정보 추출 및 기본값 설정
        $page = (int) $request->query('page', 1);
        $size = (int) $request->query('size', 20);

        // 페이지네이션 조회
        $paginatedTripDays = $this->tripDayService->paginateByTripDays(
            $trip,
            $page,
            $size
        );

        // 성공응답 반환
        return response()->json([
            'success' => true,
            'code' => 'SUCCESS',
            'message' => 'Trip Day 목록 조회에 성공했습니다',
            'data' => [
                'items' => TripDayResource::collection($paginatedTripDays->items()),
                'pagination' => [
                    'page' => $paginatedTripDays->currentPage(),
                    'size' => $paginatedTripDays->perPage(),
                    'total' => $paginatedTripDays->total(),
                    'last_page' => $paginatedTripDays->lastPage(),
                ],
            ],
        ]);
    }

    /**
     * 2, TripDay 생성
     * -  POST /v2/trips/{trip_id}/days
     * - 중간 삽입 포함
     * @param TripDayStoreRequest $request
     * @param int $tripId
     * @return JsonResponse
     */
    public function store(
        TripDayStoreRequest $request,
        int $tripId
    ): JsonResponse
    {
        // 현재 로그인 사용자의 Trip인지 확인
        $trip = $this->tripService->getOwnedTripOrFail($tripId);

        // 유효성 검사된 데이터 가져오기
        $validated = $request->validated();
        $dayNo = (int)$validated['day_no'];
        $memo = $validated['memo'] ?? null;

        // TripDay 생성
        $tripDay = $this->tripDayService->createTripDay(
            $trip,
            $dayNo,
            $memo
        );

        // 성공응답 반환
        return response()->json([
            'success' => true,
            'code' => 'SUCCESS',
            'message' => 'Trip Day 생성에 성공했습니다',
            'data' => new TripDayResource($tripDay),
        ], 201);
    }

    /**
     * 3. TripDay 단건 조회
     * - GET /v2/trips/{trip_id}/days/{day_no}
     * @param int $tripId
     * @param int $dayNo
     * @return JsonResponse
     */
    public function show(
        int $tripId,
        int $dayNo
    ): JsonResponse{
        // 현재 로그인 사용자의 Trip인지 확인
        $trip = $this->tripService->getOwnedTripOrFail($tripId);

        // TripDay 단건 조회
        $tripDay = $this->tripDayService->getTripDay(
            $trip,
            $dayNo
        );

        // 성공응답 반환
        return response()->json([
            'success' => true,
            'code' => 'SUCCESS',
            'message' => 'Trip Day 단건 조회에 성공했습니다',
            'data' => new TripDayResource($tripDay),
        ]);
    }

    /**
     * 4. TripDay 메모 수정
     * - PATCH /v2/trips/{trip_id}/days/{day_no}
     * @param TripDayUpdateRequest $request
     * @param int $tripId
     * @param int $dayNo
     * @return JsonResponse
     */
    public function updateMemo(
        TripDayUpdateRequest $request,
        int $tripId,
        int $dayNo
    ): JsonResponse {
        // 현재 로그인 사용자의 Trip인지 확인
        $trip = $this->tripService->getOwnedTripOrFail($tripId);

        // 유효성 검사된 데이터 가져오기
        $validated = $request->validated();
        $memo = $validated['memo'] ?? null;

        // TripDay 메모 수정
        $this->tripDayService->updateTripDayMemo(
            $trip,
            $dayNo,
            $memo
        );

        // 수정 된 TripDay 다시 조회
        $tripDay = $this->tripDayService->getTripDay(
            $trip,
            $dayNo
        );

        // 성공응답 반환
        return response()->json([
            'success' => true,
            'code' => 'SUCCESS',
            'message' => 'Trip Day 메모 수정에 성공했습니다',
            'data' => new TripDayResource($tripDay),
        ]);
    }

    /**
     * 5. TripDay 삭제
     * - DELETE /v2/trips/{trip_id}/days/{day_no}
     * @param int $tripId
     * @param int $dayNo
     * @return JsonResponse
     */
    public function destroy(
        int $tripId,
        int $dayNo
    ): JsonResponse{
        // 현재 로그인 사용자의 Trip인지 확인
        $trip = $this->tripService->getOwnedTripOrFail($tripId);

        // TripDay 삭제
        $this->tripDayService->deleteTripDay(
            $trip,
            $dayNo
        );

        // 성공응답 반환
        return response()->json([
            'success' => true,
            'code' => 'SUCCESS',
            'message' => 'Trip Day 삭제에 성공했습니다',
            'data' => null,
        ]);
    }

    /**
     * 6. TripDay 재정렬
     * - POST /v2/trips/{trip_id}/days/reorder
     * @param TripDayReorderRequest $request
     * @param int $tripId
     * @return JsonResponse
     */    
    public function reorder(
        TripDayReorderRequest $request,
        int $tripId
    ): JsonResponse {

        // 현재 로그인 사용자의 Trip인지 확인
        $trip = $this->tripService->getOwnedTripOrFail($tripId);

        // 유효성 검사된 데이터 가져오기
        $validated = $request->validated();
        $newOrder = $validated['new_day_no'];
        $oldOrder = $validated['old_day_no'];

        // TripDay 재정렬
        $this->tripDayService->reorderTripDay(
            $trip,
            $oldOrder,
            $newOrder
        );

        // 성공응답 반환
        return response()->json([
            'success' => true,
            'code' => 'SUCCESS',
            'message' => 'Trip Day 재정렬에 성공했습니다',
            'data' => null,
        ]);
    }
}