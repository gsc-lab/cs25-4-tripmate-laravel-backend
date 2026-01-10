<?php
namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PlaceResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'place_id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: '장소명'),
        new OA\Property(property: 'address', type: 'string', example: '주소'),
        new OA\Property(property: 'lat', type: 'number', example: 37.5),
        new OA\Property(property: 'lng', type: 'number', example: 127.0),
    ]
)]
class PlaceResponseSchema {}