<?php
namespace App\Http\Controllers\Trip;

use App\Http\Controllers\Controller;
use App\Http\Requests\TripDay\TripDayIndexRequest;
use App\Http\Requests\TripDay\TripDayReorderRequest;
use App\Http\Requests\TripDay\TripDayStoreRequest;
use App\Http\Requests\TripDay\TripDayUpdateRequest;
use App\Http\Resources\TripDayResource;
use App\Services\Trip\TripDayService;
use App\Services\Trip\TripService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

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
     *
     * @return TripDayIndexRequest $request
     */
    #[OA\Get(
        path: '/api/v2/trips/{trip_id}/days',
        summary: 'TripDay 목록 조회',
        tags: ['TripDays'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'trip_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1)),
            new OA\Parameter(name: 'size', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100)),
        ],
        responses: [
            new OA\Response(response: 200, description: '성공'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ]
    )]
    public function index(
        TripDayIndexRequest $request,
        int $tripId
    ): JsonResponse {
        // 현재 로그인 사용자의 Trip인지 확인
        $trip = $this->tripService->getOwnedTripOrFail($tripId);

        $payload = $request->payload();

        // 페이지네이션 조회
        $paginatedTripDays = $this->tripDayService->paginate(
            $trip, 
            $payload['page'], 
            $payload['size']
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
     */
    #[OA\Post(
        path: '/api/v2/trips/{trip_id}/days',
        summary: 'TripDay 생성',
        tags: ['TripDays'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'trip_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/TripDayCreateRequest')
        ),
        responses: [
            new OA\Response(response: 201, description: '생성 성공', content: new OA\JsonContent(ref: '#/components/schemas/TripDaySingleResponse')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ]
    )]
    public function store(
        TripDayStoreRequest $request,
        int $tripId
    ): JsonResponse {
        // 현재 로그인 사용자의 Trip인지 확인
        $trip = $this->tripService->getOwnedTripOrFail($tripId);

        $payload = $request->payload();

        // TripDay 생성
        $tripDay = $this->tripDayService->store(
            $trip,
            $payload['day_no'],
            $payload['memo']
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
     */
    #[OA\Get(
        path: '/api/v2/trips/{trip_id}/days/{$dayNo}',
        summary: 'TripDay 단건 조회',
        tags: ['TripDays'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'trip_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: '$dayNo', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: '성공', content: new OA\JsonContent(ref: '#/components/schemas/TripDaySingleResponse')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ]
    )]
    public function show(
        int $tripId,
        int $dayNo
    ): JsonResponse {
        // 현재 로그인 사용자의 Trip인지 확인
        $trip = $this->tripService->getOwnedTripOrFail($tripId);

        // TripDay 단건 조회
        $tripDay = $this->tripDayService->show($trip, $dayNo);

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
     */
    #[OA\Patch(
        path: '/api/v2/trips/{trip_id}/days/{$dayNo}',
        summary: 'TripDay 수정',
        tags: ['TripDays'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'trip_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: '$dayNo', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/TripDayUpdateRequest')
        ),
        responses: [
            new OA\Response(response: 200, description: '수정 성공', content: new OA\JsonContent(ref: '#/components/schemas/TripDaySingleResponse')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ]
    )]
    public function updateMemo(
        TripDayUpdateRequest $request,
        int $tripId,
        int $dayNo
    ): JsonResponse {
        // 현재 로그인 사용자의 Trip인지 확인
        $trip = $this->tripService->getOwnedTripOrFail($tripId);

        $payload = $request->payload();

        // TripDay 메모 수정
        $this->tripDayService->update($trip, $dayNo, $payload['memo']);

        // 수정된 TripDay 다시 조회
        $tripDay = $this->tripDayService->show($trip, $dayNo);

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
     */
    #[OA\Delete(
        path: '/api/v2/trips/{trip_id}/days/{$dayNo}',
        summary: 'TripDay 삭제',
        tags: ['TripDays'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'trip_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: '$dayNo', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: '삭제 성공', content: new OA\JsonContent(ref: '#/components/schemas/TripDayNullDataResponse')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ]
    )]
    public function destroy(
        int $tripId,
        int $dayNo
    ): JsonResponse {
        // 현재 로그인 사용자의 Trip인지 확인
        $trip = $this->tripService->getOwnedTripOrFail($tripId);

        // TripDay 삭제
        $this->tripDayService->destroy($trip, $dayNo);


        // 성공응답 반환
        return response()->json([
            'success' => true,
            'code' => 'SUCCESS',
            'message' => 'Trip Day 삭제에 성공했습니다',
            'data' => null,
        ]);
    }

    /**
     * 6. TripDay 전체 재배치
     * - POST /v2/trips/{trip_id}/days/reorder
     */
    #[OA\Post(
        path: '/api/v2/trips/{trip_id}/days/reorder',
        summary: 'TripDay 재배치',
        tags: ['TripDays'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'trip_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/TripDayReorderRequest')
        ),
        responses: [
            new OA\Response(response: 200, description: '성공', content: new OA\JsonContent(ref: '#/components/schemas/TripDayListResponse')),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ]
    )]
    public function reorder(
        TripDayReorderRequest $request,
        int $tripId
    ): JsonResponse {

        // 현재 로그인 사용자의 Trip인지 확인
        $trip = $this->tripService->getOwnedTripOrFail($tripId);
        
        $payload = $request->payload();

        $this->tripDayService->reorder($trip, $payload['day_ids']);

        // TripDay 재배치
        $this->tripDayService->reorder(
            $trip,
            $payload['day_ids']
        );

        // 성공응답 반환
        return response()->json([
            'success' => true,
            'code' => 'SUCCESS',
            'message' => 'Trip Day 재배치에 성공했습니다',
            'data' => null,
        ]);
    }
}