<?php

namespace App\Http\Requests\Place;

use Illuminate\Foundation\Http\FormRequest;

class PlaceGeocodeRequest extends FormRequest
{
    /**
     * 로그인 사용자 접근 허용
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 좌표 -> 주소 유효성 검증
     * - lat, lng 검증
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    public function messages(): array
    {
        return [
            'lat.required' => '위도(lat) 값을 입력해주세요.',
            'lat.numeric' => '위도 값은 숫자여야 합니다.',
            'lat.between' => '위도는 -90에서 90 사이여야 합니다.',

            'lng.required' => '경도(lng) 값을 입력해주세요.',
            'lng.numeric' => '경도 값은 숫자여야 합니다.',
            'lng.between' => '경도는 -180에서 180 사이여야 합니다.',
        ];
    }
}
