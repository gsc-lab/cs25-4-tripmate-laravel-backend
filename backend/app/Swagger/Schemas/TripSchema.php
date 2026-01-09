<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Trip',
    type: 'object',
    properties: [
        new OA\Property(property: 'trip_id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 7),
        new OA\Property(property: 'title', type: 'string', example: '오사카 2박3일'),
        new OA\Property(property: 'region_id', type: 'integer', nullable: true, example: 3),
        new OA\Property(property: 'start_date', type: 'string', format: 'date', nullable: true, example: '2026-01-10'),
        new OA\Property(property: 'end_date', type: 'string', format: 'date', nullable: true, example: '2026-01-12'),
        new OA\Property(property: 'day_count', type: 'integer', nullable: true, example: 3),
        new OA\Property(property: 'memo', type: 'string', nullable: true, example: '교통패스 구매'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class TripSchema {}

#[OA\Schema(
    schema: 'TripPagination',
    type: 'object',
    properties: [
        new OA\Property(property: 'page', type: 'integer', example: 1),
        new OA\Property(property: 'size', type: 'integer', example: 20),
        new OA\Property(property: 'total', type: 'integer', example: 37),
        new OA\Property(property: 'last_page', type: 'integer', example: 2),
    ]
)]
class TripPaginationSchema {}

#[OA\Schema(
    schema: 'TripListData',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'items',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Trip')
        ),
        new OA\Property(property: 'pagination', ref: '#/components/schemas/TripPagination'),
    ]
)]
class TripListDataSchema {}

#[OA\Schema(
    schema: 'TripListResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'code', type: 'string', example: 'SUCCESS'),
        new OA\Property(property: 'message', type: 'string', example: 'Trip 목록 조회에 성공했습니다'),
        new OA\Property(property: 'data', ref: '#/components/schemas/TripListData'),
    ]
)]
class TripListResponseSchema {}

#[OA\Schema(
    schema: 'TripSingleResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'code', type: 'string', example: 'SUCCESS'),
        new OA\Property(property: 'message', type: 'string', example: 'Trip 단건 조회에 성공했습니다'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Trip'),
    ]
)]
class TripSingleResponseSchema {}

#[OA\Schema(
    schema: 'TripCreateRequest',
    type: 'object',
    required: ['title'],
    properties: [
        new OA\Property(property: 'title', type: 'string', maxLength: 100, example: '오사카 2박3일'),
        new OA\Property(property: 'region_id', type: 'integer', nullable: true, example: 3),
        new OA\Property(property: 'start_date', type: 'string', format: 'date', nullable: true, example: '2026-01-10'),
        new OA\Property(property: 'end_date', type: 'string', format: 'date', nullable: true, example: '2026-01-12'),
        new OA\Property(property: 'memo', type: 'string', nullable: true, maxLength: 255, example: '교통패스 구매'),
    ]
)]
class TripCreateRequestSchema {}

#[OA\Schema(
    schema: 'TripUpdateRequest',
    type: 'object',
    properties: [
        new OA\Property(property: 'title', type: 'string', maxLength: 100, nullable: true),
        new OA\Property(property: 'region_id', type: 'integer', nullable: true),
        new OA\Property(property: 'start_date', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'end_date', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'memo', type: 'string', nullable: true, maxLength: 255),
    ]
)]
class TripUpdateRequestSchema {}

#[OA\Schema(
    schema: 'TripNullDataResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'code', type: 'string', example: 'SUCCESS'),
        new OA\Property(property: 'message', type: 'string', example: '처리 성공'),
        new OA\Property(property: 'data', nullable: true, example: null),
    ]
)]
class TripNullDataResponseSchema {}
