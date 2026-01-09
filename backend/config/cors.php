<?php

return [

    // CORS 적용 경로
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // 허용 메서드
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    // 허용 Origin
    // 프론트 주소: http://localhost:5173
    'allowed_origins' => ['http://localhost:5173'],

    'allowed_origins_patterns' => [],

    // 허용 헤더
    'allowed_headers' => [
        'Content-Type',
        'X-Requested-With',
        'Authorization',
        'Origin',
        'Accept',
    ],

    // 노출할 헤더
    'exposed_headers' => ['Authorization'],

    // Preflight 캐시 시간
    'max_age' => 3600,

    // 쿠키/세션 등 credentials 허용
    'supports_credentials' => true,
];
