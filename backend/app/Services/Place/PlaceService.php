<?php

namespace App\Services\Place;

use App\Repositories\Place\PlaceRepository;
use Illuminate\Support\Facades\Http;

class PlaceService
{
    private PlaceRepository $repository;

    public function __construct(PlaceRepository $placeRepository)
    {
        $this->repository = $placeRepository;
    }

    /**
     * API 키를 가져오는 내부 헬퍼 메서드
     *
     * @param  array  $data
     *
     * @throws \Exception
     */
    private function getApiKey()
    {
        $key = config('services.googleApi.api_key');

        if (empty($key)) {
            throw new \Exception('Google Maps API KEY 가 설정되지 않아 실행에 실패하였습니다.');
        }

        return $key;
    }

    private function getHeaders(?string $fieldMask = null)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Goog-Api-Key' => $this->getApiKey(),
        ];

        if ($fieldMask) {
            $headers['X-Goog-FieldMask'] = config("services.google_places.field_masks.{$fieldMask}");
        }

        return $headers;
    }

    /**
     * autoplace 자동검색완성기능 서비스
     *
     * @param  mixed  $place
     * @param  mixed  $sessionToken
     *
     * @throws \Exception
     */
    public function autoPlace($place, $sessionToken = null)
    {
        $url = config('services.google_places.endpoints.autocomplete');

        $postData = [
            'input' => $place,
            'languageCode' => 'ko',
        ];

        // 세션있을 경우 추가
        if ($sessionToken) {
            $postData['sessionToken'] = $sessionToken;
        }

        // API 요청
        $response = Http::withHeaders($this->getHeaders())->post($url, $postData);

        if ($response->successful()) {
            return $response->json();
        } else {
            throw new \Exception('자동검색 API 호출에 실패하였습니다.');
        }
    }

    /**
     * search 외부api를 이용해 장소 검색
     *
     * @param  mixed  $place
     * @param  mixed  $pageToken
     * @param  mixed  $sort
     *
     * @throws \Exception
     */
    public function search($place, $pageToken, $sort)
    {
        // 본문 작성
        $postData = [
            'languageCode' => 'ko',
            'pageSize' => 10,
        ];

        // 페이지네이션
        if (! empty($pageToken)) {
            $postData['pageToken'] = $pageToken; // pagetoken만 사용(textQuery 사용 불가)
        } else {
            $postData['textQuery'] = $place; // 첫 페이지는 place로 요청
        }

        // URI
        $url = config('services.google_places.endpoints.text_search');

        // API 연결
        $response = Http::withHeaders($this->getHeaders('search'))->post($url, $postData);

        // 응답처리
        if ($response->successful()) {
            return $response->json();
        } else {
            throw new \Exception('장소 검색 도중 알 수 없는 에러가 발생했습니다. : ');
        }

    }

    /**
     * selected Place 장소 단건 조회
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function find(int $placeId)
    {
        return $this->repository->findById($placeId);
    }

    /**
     * Geocoding reverse 좌표를 주소로 변환
     *
     * @param  mixed  $lat
     * @param  mixed  $lng
     *
     * @throws \Exception
     */
    public function reverse($lat, $lng)
    {
        // 쿼리 작성
        $params = [
            'latlng' => "$lat, $lng",
            'key' => $this->getApiKey(),
            'language' => 'ko',
            'result_type' => 'street_address|premise',
        ];

        // URI 작성
        $url = config('services.google_places.endpoints.reverse_geocoding');

        // API 요청
        $response = Http::get($url, $params);

        if ($response->successful()) {
            $data = $response->json();

            // 응답 처리
            if ($data['status'] === 'OK') {
                return $data['results'][0]['formatted_address'];
            } else {
                return null;
            }
            // 좌표가 없을 경우
        } else {
            throw new \Exception('좌표를 주소로 반환할 수 없습니다.');
        }
    }

    /**
     * geocode 주소를 이용하여 장소로 변환
     *
     * @throws \Exception
     */
    public function geocode(string $placeId)
    {
        // 쿼리
        $params = ['languageCode' => 'ko'];

        // uri 작성
        $url = "https://places.googleapis.com/v1/places/{$placeId}";

        // API 요청
        $response = Http::withHeaders($this->getHeaders('place_details'))->get($url, $params);

        // 응답 반환
        if ($response->successful()) {
            return $response->json();
        } else {
            throw new \Exception('주소를 장소로 변경하는 데에 실패하였습니다.');
        }
    }

    /**
     * nearby 주변 지역 검색 후 장소 반환
     *
     * @param  mixed  $lat
     * @param  mixed  $lng
     * @param  mixed  $radius
     *
     * @throws \Exception
     */
    public function nearby($lat, $lng, $radius = 1000)
    {
        $url = config('services.google_places.endpoints.nearby');

        $postData = [
            'languageCode' => 'ko',
            'maxResultCount' => 20,
            'locationRestriction' => [
                'circle' => [
                    'center' => [
                        'latitude' => (float) $lat,
                        'longitude' => (float) $lng,
                    ],
                    'radius' => (float) $radius, // 미터 단위
                ],
            ],
        ];

        // API 요청
        $response = Http::withHeaders($this->getHeaders('nearby'))->post($url, $postData);

        // 반환 값
        if ($response->successful()) {
            return $response->json();
        } else {
            throw new \Exception('주변 장소 검색에 실패하였습니다.');
        }
    }

    /**
     * create 외부 결과 내부로 저장
     */
    public function create(array $data)
    {
        $result = $this->repository->update($data);

        return $result;
    }
}
