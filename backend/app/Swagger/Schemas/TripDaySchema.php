<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TripDay',
    type: 'object',
    properties: [
        new OA\Property(property: 'trip_day_id', type: 'integer', example: 11),
        new OA\Property(property: 'trip_id', type: 'integer', example: 1),
        new OA\Property(property: 'day_no', type: 'integer', example: 2),
        new OA\Property(property: 'memo', type: 'string', nullable: true, example: '둘째날 메모'),
        new OA\Property(property: 'date', type: 'string', format: 'date', nullable: true, example: '2026-01-07'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class TripDaySchema {}

#[OA\Schema(
    schema: 'TripDayPagination',
    type: 'object',
    properties: [
        new OA\Property(property: 'page', type: 'integer', example: 1),
        new OA\Property(property: 'size', type: 'integer', example: 20),
        new OA\Property(property: 'total', type: 'integer', example: 37),
        new OA\Property(property: 'last_page', type: 'integer', example: 2),
    ]
)]
class TripDayPaginationSchema {}

#[OA\Schema(
    schema: 'TripDayListData',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'items',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/TripDay')
        ),
        new OA\Property(property: 'pagination', ref: '#/components/schemas/TripDayPagination'),
    ]
)]
class TripDayListDataSchema {}

#[OA\Schema(
    schema: 'TripDayListResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'code', type: 'string', example: 'SUCCESS'),
        new OA\Property(property: 'message', type: 'string', example: 'Trip Day 목록 조회에 성공했습니다'),
        new OA\Property(property: 'data', ref: '#/components/schemas/TripDayListData'),
    ]
)]
class TripDayListResponseSchema {}

#[OA\Schema(
    schema: 'TripDaySingleResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'code', type: 'string', example: 'SUCCESS'),
        new OA\Property(property: 'message', type: 'string', example: 'Trip Day 단건 조회에 성공했습니다'),
        new OA\Property(property: 'data', ref: '#/components/schemas/TripDay'),
    ]
)]
class TripDaySingleResponseSchema {}

#[OA\Schema(
    schema: 'TripDayCreateRequest',
    type: 'object',
    required: ['day_no'],
    properties: [
        new OA\Property(property: 'day_no', type: 'integer', minimum: 1, example: 2),
        new OA\Property(property: 'memo', type: 'string', nullable: true, maxLength: 255, example: '메모'),
    ]
)]
class TripDayCreateRequestSchema {}

#[OA\Schema(
    schema: 'TripDayUpdateMemoRequest',
    type: 'object',
    properties: [
        new OA\Property(property: 'memo', type: 'string', nullable: true, maxLength: 255, example: '수정 메모'),
    ]
)]
class TripDayUpdateMemoRequestSchema {}

#[OA\Schema(
    schema: 'TripDayReorderRequest',
    type: 'object',
    required: ['day_ids'],
    properties: [
        new OA\Property(
            property: 'day_ids',
            type: 'array',
            minItems: 1,
            items: new OA\Items(type: 'integer'),
            example: [31, 29, 30]
        ),
    ]
)]
class TripDayReorderRequestSchema {}

#[OA\Schema(
    schema: 'TripDayNullDataResponse',
    type: 'object',
    required: ['success', 'code', 'message', 'data'],
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'code', type: 'string', example: 'SUCCESS'),
        new OA\Property(property: 'message', type: 'string', example: 'TripDay 삭제에 성공했습니다'),
        new OA\Property(property: 'data', nullable: true, example: null),
    ]
)]

#[OA\Schema(
    schema: 'TripDayUpdateRequest',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'memo',
            type: 'string',
            nullable: true,
            example: '오늘 일정 메모'
        ),
    ]
)]
class TripDayNullDataResponseSchema {}
