<?php

namespace App\Http\Controllers\Trip;

use App\Http\Controllers\Controller;
use App\Http\Requests\Trip\TripIndexRequest;
use App\Http\Requests\Trip\TripStoreRequest;
use App\Http\Requests\Trip\TripUpdateRequest;
use App\Http\Resources\TripResource;
use App\Services\Trip\TripService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

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
     */
    #[OA\Get(
        path: '/api/v2/trips',
        summary: 'Trip 목록 조회',
        tags: ['Trips'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1)),
            new OA\Parameter(name: 'size', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100)),
            new OA\Parameter(name: 'region_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '성공',
                content: new OA\JsonContent(ref: '#/components/schemas/TripListResponse')
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        ]
    )]
    public function index(TripIndexRequest $request): JsonResponse
    {
        // FormRequest에서 검증된 데이터 가져오기
        $payload = $request->payload();

        // 페이지네이션 처리된 Trip 목록 조회
        $paginatoredTrips = $this->tripService->paginate(
            $payload['page'],
            $payload['size'],
            $payload['sort'],
        $payload['region_id']
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
     */
    #[OA\Post(
        path: '/api/v2/trips',
        summary: 'Trip 생성',
        tags: ['Trips'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/TripCreateRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: '생성 성공',
                content: new OA\JsonContent(ref: '#/components/schemas/TripSingleResponse')
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ]
    )]
    public function store(TripStoreRequest $request): JsonResponse
    {
        // FormRequest에서 검증된 데이터 가져오기
        $payload = $request->payload();

        // Trip 생성 서비스 호출
        $trip = $this->tripService->store($payload);

        // 응답 반환
        return response()->json([
            'success' => true,
            'data' => new TripResource($trip),
        ], 201);
    }

    /**
     * 3. 단일 Trip 조회
     * - GET /v2/trips/{trip}
     */
    #[OA\Get(
        path: '/api/v2/trips/{trip}',
        summary: 'Trip 단건 조회',
        tags: ['Trips'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'trip',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '성공',
                content: new OA\JsonContent(ref: '#/components/schemas/TripSingleResponse')
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ]
    )]
    public function show(int $trip): JsonResponse
    {
        // Trip 조회 서비스 호출
        $tripModel = $this->tripService->show($trip);

        // 응답 반환
        return response()->json([
            'success' => true,
            'data' => new TripResource($tripModel),
        ]);
    }

    /**
     * 4. Trip 업데이트
     * PATCH /v2/trips/{trip}
     *
     * @param  int  $tripId
     */
    #[OA\Patch(
        path: '/api/v2/trips/{trip}',
        summary: 'Trip 수정',
        tags: ['Trips'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'trip', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/TripUpdateRequest')),
        responses: [
            new OA\Response(
                response: 200,
                description: '수정 성공',
                content: new OA\JsonContent(ref: '#/components/schemas/TripSingleResponse')
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ]
    )]
    public function update(TripUpdateRequest $request, int $trip) : JsonResponse
    {
        // FormRequest에서 검증된 데이터 가져오기
        $payload = $request->payload();

        // Trip 업데이트 서비스 호출
        $updatedTrip = $this->tripService->update($trip, $payload);

        // 응답 반환
        return response()->json([
            'success' => true,
            'data' => new TripResource($updatedTrip),
        ]);
    }

    /**
     * 5. Trip 삭제
     * DELETE /v2/trips/{trip}
     *
     * @param  int  $tripId
     */
    #[OA\Delete(
        path: '/api/v2/trips/{trip}',
        summary: 'Trip 삭제',
        tags: ['Trips'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'trip', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '삭제 성공',
                content: new OA\JsonContent(ref: '#/components/schemas/TripNullDataResponse')
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ]
    )]
    public function destroy(int $trip): JsonResponse
    {
        // Trip 삭제 서비스 호출
        $this->tripService->destroy($trip);

        // 응답 반환
        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Trip 삭제에 성공하였습니다',
        ]);
    }
}