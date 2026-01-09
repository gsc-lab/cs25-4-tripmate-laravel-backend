<?php

namespace App\Services\Trip;

class DistanceHelper
{
    /**
     * 두 지점(위도, 경도) 간의 직선 거리 계산 (Haversine Formula)
     *
     * @param  float  $lat1  출발지 위도
     * @param  float  $lng1  출발지 경도
     * @param  float  $lat2  도착지 위도
     * @param  float  $lng2  도착지 경도
     * @return float 거리 (km 단위, 소수점 2자리 반올림)
     */
    public static function calculate($lat1, $lng1, $lat2, $lng2): float
    {
        // 1. 데이터 유효성 검사 (좌표가 없으면 0km 반환)
        if (empty($lat1) || empty($lng1) || empty($lat2) || empty($lng2)) {
            return 0;
        }

        // 2. 지구 반지름 설정 (단위: km)
        $earthRadius = 6371;

        // 3. 위도와 경도의 차이 구하기 (단위: 라디안)
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lng2 - $lng1);

        // 4. 하버사인 공식 (a 값 계산)
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        // 5. 각도(c) 계산 (역탄젠트)
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        // 6. 최종 거리 계산
        // 반지름(R) x 각도(c) = 호의 길이(거리)
        $distance = $earthRadius * $c;

        // 7. 소수점 2자리 반올림
        return round($distance, 2);
    }
}
