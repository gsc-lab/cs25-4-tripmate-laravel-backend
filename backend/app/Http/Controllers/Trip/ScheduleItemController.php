<?php
namespace App\Http\Controllers\Trip;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleItem\ScheduleItemIndexRequest;
use App\Http\Requests\ScheduleItem\ScheduleItemReorderRequest;
use App\Http\Requests\ScheduleItem\ScheduleItemStoreRequest;
use App\Http\Requests\ScheduleItem\ScheduleItemPatchRequest;
use App\Http\Requests\ScheduleItem\ScheduleItemPutRequest;
use App\Http\Resources\ScheduleItemResource;
use App\Services\Trip\ScheduleItemService;
use App\Services\Trip\TripService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class ScheduleItemController extends Controller
{
    // service 프로퍼티 정의
    protected ScheduleItemService $scheduleItemService;

    protected TripService $tripService;

    // 생성자에서 서비스 주입
    public function __construct(
        ScheduleItemService $scheduleItemService,
        TripService $tripService
    ) {
        $this->scheduleItemService = $scheduleItemService;
        $this->tripService = $tripService;
    }

    /**
     * 1. 일정 아이템 목록 조회 (페이지네이션)
     */
    #[OA\Get(
        path: '/api/v2/trips/{trip_id}/days/{trip_day_id}/schedule-items',
        summary: 'ScheduleItem 목록 조회',
        tags: ['ScheduleItems'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'trip_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'trip_day_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
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
        ScheduleItemIndexRequest $request,
        int $tripId,
        int $tripDayId
    ): JsonResponse {
        // 본인 소유 trip 인지 확인
        $trip = $this->tripService->getOwnedTripOrFail($tripId);

        $pagination = $request->payload();

        // 페이지네이션 조회
        $paginated = $this->scheduleItemService->paginateScheduleItems(
            $trip, 
            $tripDayId, 
            $pagination
        );

        $detail = $this->scheduleItemService->calculateRouteDistancesByDistance($trip, $tripDayId);
        $latlng = $this->scheduleItemService->getlatlng($trip, $tripDayId);

        // 성공응답 반환
        return response()->json([
            'success' => true,
            'code' => 'SUCCESS',
            'message' => '일정 아이템 목록 조회에 성공했습니다',
            'data' => [
                'items' => ScheduleItemResource::collection($paginated->items()),
                'pagination' => [
                    'page' => $paginated->currentPage(),
                    'size' => $paginated->perPage(),
                    'total' => $paginated->total(),
                    'last_page' => $paginated->lastPage(),
                ],
                'detail' => $detail,
                'latlng' => $latlng,
            ],
        ]);
    }

    /**
     * 2. 일정 아이템 생성
     */
    #[OA\Post(
        path: '/api/v2/trips/{trip_id}/days/{trip_day_id}/schedule-items',
        summary: 'ScheduleItem 생성',
        tags: ['ScheduleItems'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'trip_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'trip_day_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ScheduleItemCreateRequest')
        ),
        responses: [
            new OA\Response(response: 201, description: '생성 성공'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ]
    )]
    public function store(
        ScheduleItemStoreRequest $request,
        int $tripId,
        int $tripDayId
    ): JsonResponse {
        // 본인 소유 trip 인지 확인
        $trip = $this->tripService->getOwnedTripOrFail($tripId);

        // 유효성 검사된 데이터 가져오기
        $payload = $request->payload();

        // 일정 아이템 생성
        $scheduleItem = $this->scheduleItemService->createScheduleItem(
            $trip,
            $tripDayId,
            $payload
        );

        // 성공응답 반환
        return response()->json([
            'success' => true,
            'code' => 'SUCCESS',
            'message' => '일정 아이템 생성에 성공했습니다',
            'data' => new ScheduleItemResource($scheduleItem),
        ], 201);
    }

    /**
     * 3. 일정 아이템 단건 조회
     */
    #[OA\Get(
        path: '/api/v2/trips/{trip_id}/days/{trip_day_id}/schedule-items/{schedule_item_id}',
        summary: 'ScheduleItem 단건 조회',
        tags: ['ScheduleItems'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'trip_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'trip_day_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'schedule_item_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: '성공'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ]
    )]
    public function show(
        int $tripId,
        int $tripDayId,
        int $itemId
    ): JsonResponse {
        // 본인 소유 trip 인지 확인
        $trip = $this->tripService->getOwnedTripOrFail($tripId);

        // 일정 아이템 단건 조회
        $scheduleItem = $this->scheduleItemService->getScheduleItem(
            $trip,
            $tripDayId,
            $itemId
        );

        // 성공응답 반환
        return response()->json([
            'success' => true,
            'code' => 'SUCCESS',
            'message' => '일정 아이템 단건 조회에 성공했습니다',
            'data' => new ScheduleItemResource($scheduleItem),
        ]);
    }

    /**
    * 4. ScheduleItem 부분 수정 (PATCH)
    */
    #[OA\Patch(
        path: '/api/v2/trips/{trip_id}/days/{trip_day_id}/schedule-items/{schedule_item_id}',
        summary: 'ScheduleItem 부분 수정',
        tags: ['ScheduleItems'],
        security: [['bearerAuth' => []]],
        parameters: [
        new OA\Parameter(name:'trip_id', in:'path', required:true, schema:new OA\Schema(type:'integer')),
        new OA\Parameter(name:'trip_day_id', in:'path', required:true, schema:new OA\Schema(type:'integer')),
        new OA\Parameter(name:'schedule_item_id', in:'path', required:true, schema:new OA\Schema(type:'integer')),
        ],
        requestBody: new OA\RequestBody(required:true, content: new OA\JsonContent(ref:'#/components/schemas/ScheduleItemPatchRequest')),
        responses: [
        new OA\Response(response:200, description:'성공'),
        new OA\Response(response:422, ref:'#/components/responses/ValidationError'),
        ]
    )]
    public function patch(
        ScheduleItemPatchRequest $request,
        int $tripId,
        int $tripDayId,
        int $itemId
    ): JsonResponse {
        $trip = $this->tripService->getOwnedTripOrFail($tripId);

        $payload = $request->payload();

        $item = $this->scheduleItemService->updateScheduleItem(
            $trip,
            $tripDayId,
            $itemId,
            $payload
        );

        return response()->json([
            'success' => true,
            'code' => 'SUCCESS',
            'message' => '일정 아이템 부분 수정에 성공했습니다',
            'data' => new ScheduleItemResource($item),
        ]);
    }
    /**
      * 5. ScheduleItem 전체 수정 (PUT)
    */
    #[OA\Put(
        path: '/api/v2/trips/{trip_id}/days/{trip_day_id}/schedule-items/{schedule_item_id}',
        summary: 'ScheduleItem 전체 수정',
        tags: ['ScheduleItems'],
        security: [['bearerAuth' => []]],
        parameters: [
        new OA\Parameter(name:'trip_id', in:'path', required:true, schema:new OA\Schema(type:'integer')),
        new OA\Parameter(name:'trip_day_id', in:'path', required:true, schema:new OA\Schema(type:'integer')),
        new OA\Parameter(name:'schedule_item_id', in:'path', required:true, schema:new OA\Schema(type:'integer')),
        ],
        requestBody: new OA\RequestBody(required:true, content: new OA\JsonContent(ref:'#/components/schemas/ScheduleItemPutRequest')),
        responses: [
        new OA\Response(response:200, description:'성공'),
        new OA\Response(response:422, ref:'#/components/responses/ValidationError'),
        ]
    )]
    public function put(
        ScheduleItemPutRequest $request,
        int $tripId,
        int $tripDayId,
        int $itemId
    ): JsonResponse {
        $trip = $this->tripService->getOwnedTripOrFail($tripId);

        $payload = $request->payload();

        $item = $this->scheduleItemService->updateScheduleItem(
            $trip,
            $tripDayId,
            $itemId,
            $payload
        );

        return response()->json([
            'success' => true,
            'code' => 'SUCCESS',
            'message' => '일정 아이템 전체 수정에 성공했습니다',
            'data' => new ScheduleItemResource($item),
        ]);
    }

    /**
     * 5. 일정 아이템 삭제
     * @param  int  $tripId
     * @param  int  $tripDayId
     * @param  int  $itemId
     */
    #[OA\Delete(
        path: '/api/v2/trips/{trip_id}/days/{trip_day_id}/schedule-items/{schedule_item_id}',
        summary: 'ScheduleItem 삭제',
        tags: ['ScheduleItems'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'trip_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'trip_day_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'schedule_item_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '삭제 성공',
                content: new OA\JsonContent(ref: '#/components/schemas/ScheduleItemNullDataResponse')
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ]
    )]
    public function destroy(
        int $tripId,
        int $tripDayId,
        int $itemId
    ): JsonResponse {
        // 본인 소유 trip 인지 확인
        $trip = $this->tripService->getOwnedTripOrFail((int) $tripId);

        // 일정 아이템 삭제
        $this->scheduleItemService->deleteScheduleItem(
            $trip,
            $tripDayId,
            $itemId
        );

        // 성공응답 반환
        return response()->json([
            'success' => true,
            'code' => 'SUCCESS',
            'message' => '일정 아이템 삭제에 성공했습니다',
            'data' => null,
        ]);
    }
    /**
     * 6. 일정 아이템 순서 변경
     * - PATCH /v2/trips/{trip_id}/days/{day_no}/items/reorder
     */
    #[OA\Post(
        path: '/api/v2/trips/{trip_id}/days/{trip_day_id}/schedule-items/reorder',
        summary: 'ScheduleItem 재배치',
        tags: ['ScheduleItems'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'trip_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'trip_day_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ScheduleItemReorderRequest')
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
        ScheduleItemReorderRequest $request,
        int $tripId
    ): JsonResponse {
        // 본인 소유 trip 인지 확인
        $trip = $this->tripService->getOwnedTripOrFail($tripId);

        /** 
         * 유효성 검사된 데이터 가져오기
         * @var array $orders 
        */
        $orders = $request->validated('orders');

        // 일정 아이템 순서 변경
        $this->scheduleItemService->reorderScheduleItems(
            $trip,
            $orders
        );

        // 성공응답 반환
        return response()->json([
            'success' => true,
            'code' => 'SUCCESS',
            'message' => '일정 아이템 재배치에 성공했습니다',
            'data' => null,
        ]);
    }
}