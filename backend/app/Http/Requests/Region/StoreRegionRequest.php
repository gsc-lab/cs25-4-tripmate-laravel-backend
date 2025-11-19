<?php

namespace App\Http\Requests\Region;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegionRequest extends FormRequest
{
    /**
     * 사용자 접근 허용
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 지역 검색 유효성검증
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'query' => ['sometimes', 'nullable', 'string'],
            'country' => ['sometimes', 'nullable', 'string']
        ];
    }

    /**
     * @return array{country.string: string, query.string: string}
     */
    public function messages(): array
    {
        return [
            'query.string' => '쿼리의 값은 반드시 문자형이어야 합니다.',
            'country.string'=> '국가코드의 값은 반드시 문자형이어야 합니다.'
        ];
    }
}
