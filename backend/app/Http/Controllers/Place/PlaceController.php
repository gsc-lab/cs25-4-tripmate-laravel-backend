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

class PlaceController extends Controller
{
    private PlaceService $service;

    public function __construct(PlaceService $service)
    {
        $this->service = $service;
    }

    /**
     * 자동검색 완성
     *
     * @return \Illuminate\Http\JsonResponse
     */
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
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function externalSearch(PlaceSearchRequest $request)
    {
        $data = $request->validated();

        // 장소 검색 데이터 받아오기
        $result = $this->service->search(
            $data['place'],
            $data['pageToken'] ?? null,
            $data['sort'] ?? null);

        // 데이터 처리
        $nextPageToken = $result['nextPageToken'] ?? null;
        $places = $result['places'] ?? [];

        // 응답
        return response()->json([
            'success' => true,
            'data' => [
                'meta' => [
                    'next_page_token' => $nextPageToken,
                ],
                'data' => ExternalPlaceResource::collection($places),
            ],
        ]);
    }

    /**
     * place select 장소 단건 조회
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPlaceById(int $id)
    {
        $result = $this->service->find($id);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * reverseGeocode 좌표를 주소로 변환
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function reverseGeocode(PlaceGeocodeRequest $request)
    {
        $data = $request->validated();

        $result = $this->service->reverse($data['lat'], $data['lng']);

        return response()->json([
            'success' => true,
            'data' => [
                'address' => $result,
            ],
        ]);
    }

    /**
     * placeGeocode 주소를 장소로 변환
     *
     * @return \Illuminate\Http\JsonResponse
     */
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
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function nearbyPlaces(PlaceGeocodeRequest $request)
    {
        $data = $request->validated();

        $result = $this->service->nearby($data['lat'], $data['lng']);

        // 빈 배열 에러 방지
        $places = $result['places'] ?? [];

        return response()->json([
            'success' => true,
            'data' => [
                'meta' => [
                    'next_page_token' => null,
                ],
                'data' => ExternalPlaceResource::collection($places),
            ],
        ]);
    }

    /**
     * create Place form External 외부 결과 저장
     *
     * @return \Illuminate\Http\JsonResponse
     */
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
