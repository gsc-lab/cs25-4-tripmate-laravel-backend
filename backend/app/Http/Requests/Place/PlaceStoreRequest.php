<?php

namespace App\Http\Requests\Place;

use Illuminate\Foundation\Http\FormRequest;

class PlaceStoreRequest extends FormRequest
{
    /**
     * 로그인 사용자 접근 허용
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 외부 결과를 내부로 저장 유효성 검증
     * - name, category, address, external_ref, lat, lng 검증
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'category' => ['required', 'string'],
            'address' => ['required', 'string'],
            'external_ref' => ['required', 'string'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * @return array{address.required: string, category.required: string, category.string: string, lat.between: string, lat.numeric: string, lat.required: string, lng.between: string, lng.numeric: string, lng.required: string, name.required: string, name.string: string}
     */
    public function messages(): array
    {
        return [
            'name.required' => '장소 이름을 입력해주세요.',
            'name.string' => '장소 이름은 문자열이어야 합니다.',

            'category.required' => '카테고리를 입력해주세요.',
            'category.string' => '카테고리는 문자열이어야 합니다.',

            'address.required' => '주소를 입력해주세요.',
            'address.string' => '주소는 문자열이어야 합니다.',

            'external_ref.required' => '외부 참조 ID는 필수입니다.',
            'external_ref.string' => '외부 참조 ID는 문자열이어야 합니다.',

            'lat.required' => '위도(lat) 값을 입력해주세요.',
            'lat.numeric' => '위도 값은 숫자여야 합니다.',
            'lat.between' => '위도는 -90에서 90 사이여야 합니다.',

            'lng.required' => '경도(lng) 값을 입력해주세요.',
            'lng.numeric' => '경도 값은 숫자여야 합니다.',
            'lng.between' => '경도는 -180에서 180 사이여야 합니다.',
        ];
    }
}
