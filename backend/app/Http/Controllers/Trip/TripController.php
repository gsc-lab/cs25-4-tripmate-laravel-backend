<?php

namespace App\Http\Controllers\Trip;

use App\Http\Controllers\Controller;
use App\Http\Requests\Trip\StoreTripRequest;
use App\Http\Requests\Trip\UpdateTripRequest;
use App\Http\Requests\Trip\ListItemRequest;
use App\Models\Trip;
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
     * @param ListItemRequest $request
     * @return JsonResponse
     */
    public function index(ListItemRequest $request) : JsonResponse
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
     * @param StoreTripRequest $request
     * @return JsonResponse
     */
    public function store(StoreTripRequest $request) : JsonResponse
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
     * - GET /v2/trips/{id}
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
     * PATCH /v2/trips/{trip}
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}