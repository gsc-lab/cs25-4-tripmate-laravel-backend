<?php

namespace App\Http\Controllers\Trip;

use App\Http\Controllers\Controller;
use App\Http\Requests\Trip\TripStoreRequest;
use App\Http\Requests\Trip\TripUpdateRequest;
use App\Http\Requests\Trip\TripIndexRequest;
use App\Http\Resources\TripResource;
use App\Services\Trip\TripService;
use Illuminate\Http\JsonResponse;


class TripController extends Controller
{   
    // trip service 프로퍼티
    protected TripService $tripService;

    // 생성자에서 trip service 주입
    public function __construct(TripService $tripService)
    {
        $this->tripService = $tripService;
    }

    /**
     * 1. Trip 목록 조회 
     * - 페이지네이션 적용
     * - GET /v2/trips
     * @param TripIndexRequest $request
     * @return JsonResponse
     */
    public function index(TripIndexRequest $request) : JsonResponse
    {
        // 쿼리 파라미터 
        $page = (int)$request->query('page', 1);
        $size = (int)$request->query('size', 20);
        $sort = $request->input('sort');
        $regionId = $request->input('regionId');

        // 페이지네이션 처리된 Trip 목록 조회
        $paginatoredTrips = $this->tripService->paginateTrips(
            $page, 
            $size, 
            $sort, 
            $regionId
        );

        // 응답 반환
        return response()->json([
            'success' => true,
            'data' => [
                'items' => TripResource::collection($paginatoredTrips->items()),
                'pagination' => [
                    'current_page' => $paginatoredTrips->currentPage(),
                    'last_page' => $paginatoredTrips->lastPage(),
                    'per_page' => $paginatoredTrips->perPage(),
                    'total' => $paginatoredTrips->total(),
                ],
            ],
        ]);
    }

    /**
     * 2. Trip 생성
     * - POST /v2/trips
     * @param TripStoreRequest $request
     * @return JsonResponse
     */
    public function store(TripStoreRequest $request) : JsonResponse
    {
        // FormRequest에서 검증된 데이터 가져오기
        $payload = $request->validated();

        // Trip 생성 서비스 호출
        $trip = $this->tripService->createTrip($payload);

        // 응답 반환
        return response()->json([
            'success' => true,
            'data' => new TripResource($trip),
        ], 201);
    }
    
    /**
     * 3. 단일 Trip 조회
     * - GET /v2/trips/{trip_id}
     * @param int $trip
     * @return JsonResponse
     */
    public function show(int $trip) : JsonResponse
    {
        // Trip 조회 서비스 호출
        $tripModel = $this->tripService->getTrip($trip);

        // 응답 반환
        return response()->json([
            'success' => true,
            'data' => new TripResource($tripModel),
        ]);
    }

    /**
     * 4. Trip 업데이트
     * PATCH /v2/trips/{trip_id}
     * @param TripUpdateRequest $request
     * @param int $tripId
     */
    public function update(TripUpdateRequest $request, int $tripId)
    {
        // FormRequest에서 검증된 데이터 가져오기
        $payload = $request->validated();

        // Trip 업데이트 서비스 호출
        $updatedTrip = $this->tripService->updateTrip($tripId, $payload);

        // 응답 반환
        return response()->json([
            'success' => true,
            'data' => new TripResource($updatedTrip),
        ]);
    }

    /**
     * 5. Trip 삭제
     * DELETE /v2/trips/{trip_id}
     * @param int $tripId
     * @return JsonResponse
     */
    public function destroy(int $tripId) : JsonResponse
    {
        // Trip 삭제 서비스 호출
        $this->tripService->deleteTrip($tripId);

        // 응답 반환
        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Trip 삭제에 성공하였습니다',
        ]);
    }
}