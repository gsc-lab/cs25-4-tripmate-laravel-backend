<?php
namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthRegisterRequest',
    type: 'object',
    required: ['name', 'email', 'password'],
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 50, example: '트립메이트'),
        new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 255, example: 'user@example.com'),
        new OA\Property(
            property: 'password', 
            type: 'string', 
            format: 'password', 
            description: '영문, 숫자, 특수문자 포함 8자 이상', 
            example: 'password123!'
        ),
    ]
)]
class AuthRegisterRequestSchema {}