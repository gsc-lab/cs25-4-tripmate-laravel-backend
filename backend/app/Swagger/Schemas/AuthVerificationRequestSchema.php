<?php
namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthVerificationRequest', // Controller에서 참조하는 이름
    type: 'object',
    required: ['password'],
    properties: [
        new OA\Property(
            property: 'password', 
            type: 'string', 
            format: 'password', 
            description: '본인 확인을 위한 비밀번호', 
            example: 'password123!'
        ),
    ]
)]
class AuthVerificationRequestSchema {}