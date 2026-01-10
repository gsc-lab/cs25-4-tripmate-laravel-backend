<?php

namespace App\Http\Controllers\Place;

use App\Http\Controllers\Controller;
use App\Http\Requests\Place\PlaceAutoCompleteRequest;
use App\Http\Requests\Place\PlaceDetailRequest;
use App\Http\Requests\Place\PlaceGeocodeRequest;
use App\Http\Requests\Place\PlaceSearchRequest;
use App\Http\Requests\Place\PlaceStoreRequest;
use App\Http\Resources\ExternalPlaceResource;
use App\Http\Resources\PlaceResource;
use App\Services\Place\PlaceService;
use OpenApi\Attributes as OA; // ★ 필수 Import

#[OA\Tag(name: 'Place', description: '장소 검색 및 관리 API')]
class PlaceController extends Controller
{
    private PlaceService $service;

    public function __construct(PlaceService $service)
    {
        $this->service = $service;
    }

    /**
     * 자동검색 완성
     */
    #[OA\Get(
        path: '/api/v2/places/autocomplete',
        summary: '장소 자동완성 검색',
        description: '입력된 키워드로 장소를 자동완성하여 제안합니다.',
        tags: ['Place'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'input', in: 'query', required: true, description: '검색어', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'session_token', in: 'query', required: true, description: '세션 토큰 (UUID)', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object', example: ['description' => '강남역', 'place_id' => 'xxx'])),
                    ]
                )
            )
        ]
    )]
    public function autocomplete(PlaceAutoCompleteRequest $request)
    {
        $data = $request->validated();
        $result = $this->service->autoPlace($data['input'], $data['session_token']);

        return response()->json([
            'success' => true,
            'data' => $result['suggestions'] ?? [],
        ]);
    }

    /**
     * Google Place API 외부 장소 검색
     */
    #[OA\Get(
        path: '/api/v2/places/search',
        summary: '외부 장소 검색 (Google)',
        description: 'Google Maps API를 통해 장소를 검색합니다.',
        tags: ['Place'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'place', in: 'query', required: true, description: '장소명', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'pageToken', in: 'query', required: false, description: '다음 페이지 토큰', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort', in: 'query', required: false, description: '정렬 기준', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object', 
                            properties: [
                                new OA\Property(property: 'meta', type: 'object', properties: [new OA\Property(property: 'next_page_token', type: 'string')]),
                                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/PlaceResponse'))
                            ]
                        ),
                    ]
                )
            )
        ]
    )]
    public function externalSearch(PlaceSearchRequest $request)
    {
        $data = $request->validated();
        $result = $this->service->search($data['place'], $data['pageToken'] ?? null, $data['sort'] ?? null);

        $nextPageToken = $result['nextPageToken'] ?? null;
        $places = $result['places'] ?? [];

        return response()->json([
            'success' => true,
            'data' => [
                'meta' => ['next_page_token' => $nextPageToken],
                'data' => ExternalPlaceResource::collection($places),
            ],
        ]);
    }

    /**
     * place select 장소 단건 조회
     */
    #[OA\Get(
        path: '/api/v2/places/{id}',
        summary: '장소 상세 조회 (DB)',
        description: '내부 DB에 저장된 장소 ID로 상세 정보를 조회합니다.',
        tags: ['Place'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: '장소 ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: '#/components/schemas/PlaceResponse'),
                    ]
                )
            )
        ]
    )]
    public function getPlaceById(int $id)
    {
        $result = $this->service->find($id);
        return response()->json(['success' => true, 'data' => $result]);
    }

    /**
     * reverseGeocode 좌표를 주소로 변환
     */
    #[OA\Get(
        path: '/api/v2/places/geocode/reverse',
        summary: '좌표 -> 주소 변환 (Reverse Geocoding)',
        tags: ['Place'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'lat', in: 'query', required: true, example: 37.5665, schema: new OA\Schema(type: 'number', format: 'float')),
            new OA\Parameter(name: 'lng', in: 'query', required: true, example: 126.9780, schema: new OA\Schema(type: 'number', format: 'float')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'address', type: 'string', example: '대한민국 서울특별시...')
                        ]),
                    ]
                )
            )
        ]
    )]
    public function reverseGeocode(PlaceGeocodeRequest $request)
    {
        $data = $request->validated();
        $result = $this->service->reverse($data['lat'], $data['lng']);

        return response()->json([
            'success' => true,
            'data' => ['address' => $result],
        ]);
    }

    /**
     * placeGeocode 주소를 장소로 변환 (Google Place Details)
     */
    #[OA\Get(
        path: '/api/v2/places/geocode/details',
        summary: 'Google Place ID로 상세 조회',
        description: 'Google Place ID를 사용하여 외부 장소 상세 정보를 가져옵니다.',
        tags: ['Place'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'place_id', in: 'query', required: true, description: 'Google Place ID', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: '#/components/schemas/PlaceResponse'),
                    ]
                )
            )
        ]
    )]
    public function placeGeocode(PlaceDetailRequest $request)
    {
        $result = $this->service->geocode($request->validated('place_id'));
        return response()->json([
            'success' => true,
            'data' => ExternalPlaceResource::make($result),
        ]);
    }

    /**
     * nearbyPlace 주변 장소 반환
     */
    #[OA\Get(
        path: '/api/v2/places/nearby',
        summary: '주변 장소 검색',
        description: '주어진 좌표 주변의 장소를 검색합니다.',
        tags: ['Place'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'lat', in: 'query', required: true, example: 37.5665, schema: new OA\Schema(type: 'number', format: 'float')),
            new OA\Parameter(name: 'lng', in: 'query', required: true, example: 126.9780, schema: new OA\Schema(type: 'number', format: 'float')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object',
                            properties: [
                                new OA\Property(property: 'meta', type: 'object'),
                                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/PlaceResponse'))
                            ]
                        ),
                    ]
                )
            )
        ]
    )]
    public function nearbyPlaces(PlaceGeocodeRequest $request)
    {
        $data = $request->validated();
        $result = $this->service->nearby($data['lat'], $data['lng']);
        $places = $result['places'] ?? [];

        return response()->json([
            'success' => true,
            'data' => [
                'meta' => ['next_page_token' => null],
                'data' => ExternalPlaceResource::collection($places),
            ],
        ]);
    }

    /**
     * create Place form External 외부 결과 저장
     */
    #[OA\Post(
        path: '/api/v2/places',
        summary: '외부 장소 저장',
        description: 'Google 등 외부 API에서 가져온 장소 정보를 내부 DB에 저장합니다.',
        tags: ['Place'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/PlaceStoreRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: '#/components/schemas/PlaceResponse'),
                    ]
                )
            )
        ]
    )]
    public function createPlaceFromExternal(PlaceStoreRequest $request)
    {
        $data = $request->validated();
        $result = $this->service->create($data);

        return response()->json([
            'success' => true,
            'data' => PlaceResource::make($result),
        ]);
    }
}