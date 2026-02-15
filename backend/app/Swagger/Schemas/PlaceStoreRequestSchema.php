<?php
namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PlaceStoreRequest',
    type: 'object',
    required: ['name', 'category', 'address', 'external_ref', 'lat', 'lng'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: '스타벅스 강남점'),
        new OA\Property(property: 'category', type: 'string', example: 'cafe'),
        new OA\Property(property: 'address', type: 'string', example: '서울시 강남구 테헤란로 123'),
        new OA\Property(property: 'external_ref', type: 'string', description: 'Google Place ID 등', example: 'ChIJ...'),
        new OA\Property(property: 'lat', type: 'number', format: 'float', example: 37.5665),
        new OA\Property(property: 'lng', type: 'number', format: 'float', example: 126.9780),
    ]
)]
class PlaceStoreRequestSchema {}