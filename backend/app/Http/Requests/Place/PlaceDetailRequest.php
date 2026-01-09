<?php

namespace App\Http\Requests\Place;

use Illuminate\Foundation\Http\FormRequest;

class PlaceDetailRequest extends FormRequest
{
    /**
     * 로그인 사용자 접근 허용
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 좌표 -> 장소 반환
     * - place_id(고유 google Place Id) 검증
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'place_id' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'place_id.required' => '장소의 id 값을 입력해주세요.',
            'place_id.string' => '장소의 id는 문자형이어야 합니다.',
        ];
    }
}
