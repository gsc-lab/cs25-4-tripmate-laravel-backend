<?php

namespace App\Http\Controllers\Trip;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

use App\Http\Requests\ScheduleItem\ScheduleItemIndexRequest;
use App\Http\Requests\ScheduleItem\ScheduleItemStoreRequest;
use App\Http\Requests\ScheduleItem\ScheduleItemUpdateRequest;
use App\Http\Requests\ScheduleItem\ScheduleItemReorderRequest;
use App\Http\Resources\ScheduleItemResource;
use App\Services\Trip\TripService;
use App\Services\Trip\ScheduleItemService;


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
     * - GET /v2/trips/{trip_id}/days/{day_no}/items
     * @param ScheduleItemIndexRequest $request
     * @param int $tripId
     * @param int $dayNo
     * @return JsonResponse
     */
    public function index(
        ScheduleItemIndexRequest $request,
        int $tripId,
        int $dayNo
    ): JsonResponse {
        // 본인 소유 trip 인지 확인
        $trip = $this->tripService->getOwnedTripOrFail($tripId);

        // 쿼리 파라미터에서 페이지네이션 정보 추출 및 기본값 설정
        $page = (int) $request->query('page', 1);
        $size = (int) $request->query('size', 20);

        // 페이지네이션 조회
        $paginatedScheduleItems = $this->scheduleItemService->paginateScheduleItems(
            $trip,
            $dayNo,
            $page,
            $size
        );

        // 성공응답 반환
        return response()->json([
            'success' => true,
            'code' => 'SUCCESS',
            'message' => '일정 아이템 목록 조회 성공했습니다',
            'data' => [
                'items' => ScheduleItemResource::collection($paginatedScheduleItems->items()),
                'pagination' => [
                    'page' => $paginatedScheduleItems->currentPage(),
                    'size' => $paginatedScheduleItems->perPage(),
                    'total' => $paginatedScheduleItems->total(),
                    'last_page' => $paginatedScheduleItems->lastPage(),
                ],
            ]
        ]);
    }

    /**
     * 2. 일정 아이템 생성
     * - POST /v2/trips/{trip_id}/days/{day_no}/items
     * @param ScheduleItemStoreRequest $request
     * @param int $tripId
     * @param int $dayNo
     * @return JsonResponse
     */
    public function store(
        ScheduleItemStoreRequest $request,
        int $tripId,
        int $dayNo
    ): JsonResponse {
        // 본인 소유 trip 인지 확인
        $trip = $this->tripService->getOwnedTripOrFail($tripId);

        // 유효성 검사된 데이터 가져오기
        $validated = $request->validated();

        $itemId = $validated['seq_no'] ?? null;
        $placeId = $validated['place_id'] ?? null;
        $visitTime = $validated['visit_time'] ?? null;
        $memo = $validated['memo'] ?? null;

        // 일정 아이템 생성
        $scheduleItem = $this->scheduleItemService->createScheduleItem(
            $trip,
            $dayNo,
            $itemId,
            $placeId,
            $visitTime,
            $memo
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
     * - GET /v2/trips/{trip_id}/days/{day_no}/items/{$item_id}
     * @param int $tripId
     * @param int $dayNo
     * @param int $itemId
     */
    public function show(
        int $tripId,
        int $dayNo,
        int $itemId
    ):JsonResponse {
        // 본인 소유 trip 인지 확인
        $trip = $this->tripService->getOwnedTripOrFail($tripId);

        // 일정 아이템 단건 조회
        $scheduleItem = $this->scheduleItemService->getScheduleItem(
            $trip,
            $dayNo,
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
     * 4. 일정 아이템 수정
     * - PATCH /v2/trips/{trip_id}/days/{day_no}/items/{$item_id}
     * - 부분 수정 (방문시간 / 메모)
     */
    public function update(
        ScheduleItemUpdateRequest $request,
        int $tripId,
        int $dayNo,
        int $itemId
    ): JsonResponse {
        // 본인 소유 trip 인지 확인
        $trip = $this->tripService->getOwnedTripOrFail($tripId);

        // 유효성 검사된 데이터 가져오기
        $validated = $request->validated();
        $visitTime = $validated['visit_time'] ?? null;
        $memo = $validated['memo'] ?? null;

        // 일정 아이템 수정
        $scheduleItem = $this->scheduleItemService->updateScheduleItem(
            $trip,
            $dayNo,
            $itemId,
            $visitTime,
            $memo
        );

        // 성공응답 반환
        return response()->json([
            'success' => true,
            'code' => 'SUCCESS',
            'message' => '일정 아이템 수정에 성공했습니다',
            'data' => new ScheduleItemResource($scheduleItem),
        ]);
    }

    /**
     * 5. 일정 아이템 삭제
     * - DELETE /v2/trips/{trip_id}/days/{day_no}/items/{$item_id}
     * @param int $tripId
     * @param int $dayNo
     * @param int $itemId
     * @return JsonResponse
     */
    public function destroy(
        string $tripId,
        string $dayNo,
        string $itemId
    ): JsonResponse{
        // 본인 소유 trip 인지 확인
        $trip = $this->tripService->getOwnedTripOrFail((int)$tripId);

        // 일정 아이템 삭제
        $this->scheduleItemService->deleteScheduleItem(
            $trip,
            (int)$dayNo,
            (int)$itemId
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
     * @param ScheduleItemReorderRequest $request
     * @param int $tripId
     * @param int $dayNo
     * @return JsonResponse
     */
    public function reorder(
        ScheduleItemReorderRequest $request,
        int $tripId,
        int $dayNo
    ): JsonResponse {
        // 본인 소유 trip 인지 확인
        $trip = $this->tripService->getOwnedTripOrFail($tripId);

        // 유효성 검사된 데이터 가져오기
        $validated = $request->validated();
        $itemId = $validated['item_id'];
        $newSeqNo = $validated['new_seq_no'];

        // 일정 아이템 순서 변경
        $this->scheduleItemService->reorderScheduleItem(
            $trip,
            $dayNo,
            $itemId,
            $newSeqNo
        );

        // 성공응답 반환
        return response()->json([
            'success' => true,
            'code' => 'SUCCESS',
            'message' => '일정 아이템 순서 변경에 성공했습니다',
            'data' => null,
        ]);
    }
}