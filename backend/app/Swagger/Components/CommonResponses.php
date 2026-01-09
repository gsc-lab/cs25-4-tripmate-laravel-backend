<?php

namespace App\Swagger\Components;

use OpenApi\Attributes as OA;

#[OA\Response(
    response: 'Unauthorized',
    description: 'UNAUTHORIZED',
    content: new OA\JsonContent(
        type: 'object',
        required: ['success', 'code', 'message', 'data'],
        properties: [
            new OA\Property(property: 'success', type: 'boolean', example: false),
            new OA\Property(property: 'code', type: 'string', example: 'UNAUTHENTICATED'),
            new OA\Property(property: 'message', type: 'string', example: '인증이 필요합니다.'),
            new OA\Property(property: 'data', nullable: true, example: null),
        ]
    )
)]
#[OA\Response(
    response: 'Forbidden',
    description: 'FORBIDDEN',
    content: new OA\JsonContent(
        type: 'object',
        required: ['success', 'code', 'message', 'data'],
        properties: [
            new OA\Property(property: 'success', type: 'boolean', example: false),
            new OA\Property(property: 'code', type: 'string', example: 'FORBIDDEN'),
            new OA\Property(property: 'message', type: 'string', example: '접근 권한이 없습니다.'),
            new OA\Property(property: 'data', nullable: true, example: null),
        ]
    )
)]
#[OA\Response(
    response: 'NotFound',
    description: 'NOT_FOUND',
    content: new OA\JsonContent(
        type: 'object',
        required: ['success', 'code', 'message', 'data'],
        properties: [
            new OA\Property(property: 'success', type: 'boolean', example: false),
            new OA\Property(property: 'code', type: 'string', example: 'NOT_FOUND'),
            new OA\Property(property: 'message', type: 'string', example: '리소스를 찾을 수 없습니다.'),
            new OA\Property(property: 'data', nullable: true, example: null),
        ]
    )
)]
#[OA\Response(
    response: 'ValidationError',
    description: 'VALIDATION_ERROR',
    content: new OA\JsonContent(
        type: 'object',
        required: ['success', 'code', 'message', 'data', 'errors'],
        properties: [
            new OA\Property(property: 'success', type: 'boolean', example: false),
            new OA\Property(property: 'code', type: 'string', example: 'VALIDATION_ERROR'),
            new OA\Property(property: 'message', type: 'string', example: '요청 데이터가 유효하지 않습니다.'),
            new OA\Property(property: 'data', nullable: true, example: null),
            new OA\Property(
                property: 'errors',
                type: 'object',
                additionalProperties: new OA\AdditionalProperties(
                    type: 'array',
                    items: new OA\Items(type: 'string')
                ),
                example: ['field' => ['The field is required.']]
            ),
        ]
    )
)]
final class CommonResponses {}
