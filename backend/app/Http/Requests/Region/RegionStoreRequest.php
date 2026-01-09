<?php

namespace App\Http\Requests\Region;

use Illuminate\Foundation\Http\FormRequest;

class RegionStoreRequest extends FormRequest
{
    /**
     * 로그인 사용자 접근 허용
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 지역 검색 유효성 검증
     * - query, country 검증
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'query' => ['nullable', 'string', 'required_without:country'],
            'country' => ['nullable', 'string', 'required_without:query'],
        ];
    }

    /**
     * @return array{country.string: string, query.string: string}
     */
    public function messages(): array
    {
        return [
            'query.string' => '쿼리의 값은 반드시 문자형이어야 합니다.',
            'query.required_without' => '지역 검색이 아닌 경우, 국가코드는 반드시 작성해야합니다.',

            'country.required_without' => '국가코드를 작성하지 않는 경우, 반드시 지역을 입력해야합니다.',
            'country.string' => '국가코드의 값은 반드시 문자형이어야 합니다.',
        ];
    }
}
