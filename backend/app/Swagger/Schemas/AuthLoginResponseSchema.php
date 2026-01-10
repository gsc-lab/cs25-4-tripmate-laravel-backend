<?php
namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthLoginResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'code', type: 'string', example: 'SUCCESS'),
        new OA\Property(property: 'message', type: 'string', example: '로그인에 성공하였습니다.'),
        new OA\Property(property: 'data', ref: '#/components/schemas/AuthTokenData'),
    ]
)]
class AuthLoginResponseSchema {}