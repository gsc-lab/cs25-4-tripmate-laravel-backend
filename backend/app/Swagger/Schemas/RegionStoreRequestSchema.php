<?php
namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RegionStoreRequest',
    type: 'object',
    description: '지역 검색 조건 (국가코드 또는 검색어 중 하나 필수)',
    required: [], // required_without 로직은 스웨거로 완벽 표현이 어려워 description으로 대체
    properties: [
        new OA\Property(
            property: 'query', 
            type: 'string', 
            description: '지역명 검색어 (국가코드가 없으면 필수)', 
            example: '서울'
        ),
        new OA\Property(
            property: 'country', 
            type: 'string', 
            description: '국가 코드 (ISO 2자리, 검색어가 없으면 필수)', 
            example: 'KR'
        ),
    ]
)]
class RegionStoreRequestSchema {}