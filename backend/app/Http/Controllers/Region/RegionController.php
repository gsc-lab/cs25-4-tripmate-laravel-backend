<?php

namespace App\Http\Controllers\Region;

use App\Http\Controllers\Controller;
use App\Http\Requests\Region\RegionStoreRequest;
use App\Http\Resources\RegionResource;
use App\Services\Region\RegionService;
use OpenApi\Attributes as OA; // ★ 필수 Import

#[OA\Tag(name: 'Regions', description: '지역 정보 및 검색')]
class RegionController extends Controller
{
    private RegionService $service;

    public function __construct(RegionService $service)
    {
        $this->service = $service;
    }

    /**
     * 지역 검색 및 목록 조회
     * - country(국가코드) 또는 query(검색어) 중 하나는 필수
     */
    #[OA\Get(
        path: '/api/v2/regions',
        summary: '지역 목록 검색',
        description: '국가 코드나 검색어를 입력하여 지역 목록을 조회합니다. (둘 중 하나 필수 입력)',
        tags: ['Regions'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'country',
                in: 'query',
                description: '국가 코드 (예: KR, JP). query가 없을 경우 필수.',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'KR')
            ),
            new OA\Parameter(
                name: 'query',
                in: 'query',
                description: '지역명 검색어. country가 없을 경우 필수.',
                required: false,
                schema: new OA\Schema(type: 'string', example: '오사카')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data', 
                            type: 'array', 
                            items: new OA\Items(ref: '#/components/schemas/RegionResponse')
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation Error (파라미터 누락)',
                content: new OA\JsonContent(ref: '#/components/responses/ValidationError')
            )
        ]
    )]
    public function listRegions(RegionStoreRequest $request)
    {
        $data = $request->validated();

        $data['country'] = $data['country'] ?? 'KR';
        $data['query'] = $data['query'] ?? null;

        $result = $this->service->regions($data['country'], $data['query']);

        return response()->json([
            'success' => true,
            'data' => RegionResource::collection($result),
        ]);
    }
}
