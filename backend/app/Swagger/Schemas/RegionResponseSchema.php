<?php
namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RegionResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'region_id', type: 'integer', example: 1),
        new OA\Property(property: 'name_en', type: 'string', example: 'Seoul'),
        new OA\Property(property: 'name_ko', type: 'string', example: '서울'),
        new OA\Property(property: 'country_code', type: 'string', example: 'KR'),
        new OA\Property(property: 'image_url', type: 'string', nullable: true, example: 'https://...'),
    ]
)]
class RegionResponseSchema {}