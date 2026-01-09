<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '2.0.0',
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
#[OA\Tag(name: 'Trips', description: 'Trip CRUD')]
#[OA\Tag(name: 'TripDays', description: 'Trip day management')]
#[OA\Tag(name: 'ScheduleItems', description: 'Schedule item management')]
final class OpenApi
{
    public const SERVER_URL = 'https://tripmate-api.test';
}
