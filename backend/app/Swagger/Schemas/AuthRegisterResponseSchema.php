<?php
namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthRegisterResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'code', type: 'string', example: 'SUCCESS'),
        new OA\Property(property: 'message', type: 'string', example: '회원가입이 완료되었습니다.'),
        new OA\Property(property: 'data', nullable: true, example: null),
    ]
)]
class AuthRegisterResponseSchema {}