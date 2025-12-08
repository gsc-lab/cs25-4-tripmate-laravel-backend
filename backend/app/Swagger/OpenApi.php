<?php
namespace App\Swagger;

use OpenApi\Attributes as OA;

class OpenApi
{
    public const SERVER_URL = 'https://tripmate-api.test';
}

#[OA\Info(
    version: '1.0.0',
    title: 'TripMate API v2',
    description: '여행 계획 관리용 API'
)]
#[OA\Server(
    url: OpenApi::SERVER_URL,
    description: 'API Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
class OpenApiDoc {}