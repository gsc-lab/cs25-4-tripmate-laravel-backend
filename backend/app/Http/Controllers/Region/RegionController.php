<?php

namespace App\Http\Controllers\Region;

use App\Http\Controllers\Controller;
use App\Http\Requests\Region\RegionStoreRequest;
use App\Http\Resources\RegionResource;
use App\Services\Region\RegionService;

class RegionController extends Controller
{
    private RegionService $service;

    public function __construct(RegionService $service)
    {
        $this->service = $service;
    }

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
