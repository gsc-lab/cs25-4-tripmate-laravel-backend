<?php

    namespace App\Http\Controllers\Place;

    use App\Http\Controllers\Controller;
    use App\Http\Requests\Place\PlaceDetailRequest;
    use App\Http\Requests\Place\PlaceGeocodeRequest;
    use App\Http\Requests\Place\PlaceSearchRequest;
    use App\Http\Requests\Place\PlaceStoreRequest;
    use App\Http\Resources\ExternalPlaceResource;
    use App\Services\Place\PlaceService;

class PlaceController extends Controller
{
    private PlaceService $service;
    public function __construct(PlaceService $service)
    {
        $this->service = $service;
    }

    /**
     * Google Place API 외부 장소 검색
     * @param PlaceSearchRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function externalSearch(PlaceSearchRequest $request) {
        $data = $request->validated();

        // 장소 검색 데이터 받아오기
        $result = $this->service->search(
            $data["place"],
        $data["pageToken"] ?? null,
             $data["sort"] ?? null);    
    
        // 데이터 처리
        $nextPageToken = $result['nextPageToken'] ?? null;
        $places = $result['places'] ?? [];
    
        // 응답
        return response()->json([
            'success' => true,
            'data'=> [
                'meta' => [
                    'next_page_token'=> $nextPageToken,
                ],
                'data' => ExternalPlaceResource::collection($places)
            ]
        ]);
    } 

    public function getPlaceById(int $id) {
        $result = $this->service->find($id);

        return response()->json([
            'success'=> true,
            'data'=> $result
        ]);
    }

    public function reverseGeocode(PlaceGeocodeRequest $request) {
        $data = $request->validated();

        $result = $this->service->reverse($data["lat"], $data["lng"]);

        return response()->json([
            "success"=> true,
            "data"=> [
                "address"=> $result
            ]
        ]);
    }

    /**
     * placeGeocode 
     * @param PlaceDetailRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeGeocode(PlaceDetailRequest $request) {
        $result = $this->service->geocode($request->validated("place_id"));

        return response()->json([
            "success"=> true,
            "data" => ExternalPlaceResource::make($result)
        ]);
    }

    public function nearbyPlaces(PlaceGeocodeRequest $request) {
        $data = $request->validated();

        $result = $this->service->nearby($data["lat"], $data["lng"]);
    
        // 빈 배열 에러 방지
        $places = $result['places'] ?? [];

        return response()->json([
            'success'=> true,
            'data'=> [
                'meta' => [
                    "next_page_token" => null,
                ],
                "data" => ExternalPlaceResource::collection($places)
            ]
        ]);
    }

    public function createPlaceFromExternal(PlaceStoreRequest $request) {
        $data = $request->validated();

        $result = $this->service->create($data);

        return response()->json([
            "success"=> true,
            "data"=> ExternalPlaceResource::make($result)
        ]);
    }

}
