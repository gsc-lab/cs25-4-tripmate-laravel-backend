<?php

namespace App\Http\Requests\Place;

use Illuminate\Foundation\Http\FormRequest;

class PlaceSearchRequest extends FormRequest
{
    /**
     * 로그인 사용자 접근 허용
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 외부 지도기반 장소 검색 유효성 검증
     * - place, pageToken(google token), sort 검증
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'place' => ['required', 'nullable', 'string'],
            'pageToken' => ['sometimes', 'nullable', 'string'],
            'sort' => ['sometimes', 'string'],
        ];
    }

    /**
     * 검색 예외 메세지
     *
     * @return array{pageToken.integer: string, place.string: string, sort.string: string}
     */
    public function messages(): array
    {
        return [
            'place.required' => '장소는 필수 입력값입니다.',
            'place.string' => '장소이름은 문자열이여야 합니다.',
            'pageToken.integer' => '페이지는 문자열이어야 합니다.',
            'sort.string' => '정렬 기준은 문자열이어야 합니다.',
        ];
    }
}
