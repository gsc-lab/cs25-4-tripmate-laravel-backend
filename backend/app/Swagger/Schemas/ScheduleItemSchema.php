<?php
namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ScheduleItem',
    type: 'object',
    properties: [
        new OA\Property(property: 'schedule_item_id', type: 'integer', example: 101),
        new OA\Property(property: 'trip_day_id', type: 'integer', example: 11),
        new OA\Property(property: 'seq_no', type: 'integer', example: 1),
        new OA\Property(property: 'visit_time', type: 'string', format: 'date-time', nullable: true, example: '12:00'),
        new OA\Property(property: 'memo', type: 'string', nullable: true, example: '점심 예약'),
        new OA\Property(property: 'place_id', type: 'integer', example: 55),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class ScheduleItemSchema {}

#[OA\Schema(
    schema: 'ScheduleItemPagination',
    type: 'object',
    properties: [
        new OA\Property(property: 'page', type: 'integer', example: 1),
        new OA\Property(property: 'size', type: 'integer', example: 20),
        new OA\Property(property: 'total', type: 'integer', example: 8),
        new OA\Property(property: 'last_page', type: 'integer', example: 1),
    ]
)]
class ScheduleItemPaginationSchema {}

#[OA\Schema(
    schema: 'LatLng',
    type: 'object',
    properties: [
        new OA\Property(property: 'lat', type: 'number', format: 'float', example: 34.6937),
        new OA\Property(property: 'lng', type: 'number', format: 'float', example: 135.5023),
    ]
)]
class LatLngSchema {}

#[OA\Schema(
    schema: 'RouteSegment',
    type: 'object',
    properties: [
        new OA\Property(property: 'from_index', type: 'integer', example: 0),
        new OA\Property(property: 'to_index', type: 'integer', example: 1),
        new OA\Property(property: 'distance_km', type: 'number', format: 'float', example: 2.35),
    ]
)]
class RouteSegmentSchema {}

#[OA\Schema(
    schema: 'RouteDistanceDetail',
    type: 'object',
    properties: [
        new OA\Property(property: 'segments', type: 'array', items: new OA\Items(ref: '#/components/schemas/RouteSegment')),
        new OA\Property(property: 'total_km', type: 'number', format: 'float', example: 12.4),
    ]
)]
class RouteDistanceDetailSchema {}

#[OA\Schema(
    schema: 'ScheduleItemListData',
    type: 'object',
    properties: [
        new OA\Property(property: 'items', type: 'array', items: new OA\Items(ref: '#/components/schemas/ScheduleItem')),
        new OA\Property(property: 'pagination', ref: '#/components/schemas/ScheduleItemPagination'),

        new OA\Property(property: 'detail', ref: '#/components/schemas/RouteDistanceDetail'),

        // latlng는 배열
        new OA\Property(property: 'latlng', type: 'array', items: new OA\Items(ref: '#/components/schemas/LatLng')),
    ]
)]
class ScheduleItemListDataSchema {}

#[OA\Schema(
    schema: 'ScheduleItemListResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'code', type: 'string', example: 'SUCCESS'),
        new OA\Property(property: 'message', type: 'string', example: '일정 아이템 목록 조회에 성공했습니다'),
        new OA\Property(property: 'data', ref: '#/components/schemas/ScheduleItemListData'),
    ]
)]
class ScheduleItemListResponseSchema {}

#[OA\Schema(
    schema: 'ScheduleItemSingleResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'code', type: 'string', example: 'SUCCESS'),
        new OA\Property(property: 'message', type: 'string', example: '일정 아이템 단건 조회에 성공했습니다'),
        new OA\Property(property: 'data', ref: '#/components/schemas/ScheduleItem'),
    ]
)]
class ScheduleItemSingleResponseSchema {}
#[OA\Schema(
    schema: 'ScheduleItemCreateRequest',
    type: 'object',
    required: ['place_id'],
    properties: [
        new OA\Property(property: 'place_id', type: 'integer', minimum: 1, example: 55),
        new OA\Property(property: 'seq_no', type: 'integer', minimum: 1, nullable: true, example: 1),
        new OA\Property(property: 'visit_time', type: 'string', nullable: true, example: '2026-01-07 10:30:00'),
        new OA\Property(property: 'memo', type: 'string', nullable: true, maxLength: 255, example: '메모'),
    ]
)]
class ScheduleItemCreateRequestSchema {}

#[OA\Schema(
    schema: 'ScheduleItemPatchRequest',
    type: 'object',
    properties: [
        new OA\Property(property: 'place_id', type: 'integer', minimum: 1, nullable: true, example: 55),
        new OA\Property(property: 'seq_no', type: 'integer', minimum: 1, nullable: true, example: 1),
        new OA\Property(property: 'visit_time', type: 'string', nullable: true, example: '2026-01-07 11:00:00'),
        new OA\Property(property: 'memo', type: 'string', nullable: true, maxLength: 255, example: '수정 메모'),
    ]
)]
class ScheduleItemPatchRequestSchema {}

#[OA\Schema(
    schema: 'ScheduleItemPutRequest',
    type: 'object',
    required: ['place_id', 'seq_no'],
    properties: [
        new OA\Property(property: 'place_id', type: 'integer', minimum: 1, example: 55),
        new OA\Property(property: 'seq_no', type: 'integer', minimum: 1, example: 1),
        new OA\Property(property: 'visit_time', type: 'string', nullable: true, example: '2026-01-07 11:00:00'),
        new OA\Property(property: 'memo', type: 'string', nullable: true, maxLength: 255, example: '수정 메모'),
    ]
)]
class ScheduleItemPutRequestSchema {}


#[OA\Schema(
    schema: 'ScheduleItemReorderRequest',
    type: 'object',
    required: ['orders'],
    properties: [
        new OA\Property(
            property: 'orders',
            type: 'array',
            minItems: 1,
            items: new OA\Items(
                type: 'object',
                required: ['trip_day_id', 'item_ids'],
                properties: [
                    new OA\Property(property: 'trip_day_id', type: 'integer', example: 11),
                    new OA\Property(
                        property: 'item_ids',
                        type: 'array',
                        minItems: 1,
                        items: new OA\Items(type: 'integer'),
                        example: [101, 102, 103]
                    ),
                ]
            )
        ),
    ]
)]
class ScheduleItemReorderRequestSchema {}

#[OA\Schema(
    schema: 'ScheduleItemNullDataResponse',
    type: 'object',
    required: ['success', 'code', 'message', 'data'],
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'code', type: 'string', example: 'SUCCESS'),
        new OA\Property(property: 'message', type: 'string', example: '일정 아이템 삭제에 성공했습니다'),
        new OA\Property(property: 'data', nullable: true, example: null),
    ]
)]
class ScheduleItemNullDataResponseSchema {}