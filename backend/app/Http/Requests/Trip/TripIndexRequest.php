<?php

namespace App\Http\Requests\Trip;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TripIndexRequest extends FormRequest
{
    /**
     * 로그인 사용자 접근 허용
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 여행 목록 유효성 검증
     * - page, size, sort, region_id 검증
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'size' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort' => ['sometimes', 'string', Rule::in(['latest', 'oldest'])],
            'region_id' => ['sometimes', 'integer', Rule::exists('regions', 'region_id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'page.integer' => '페이지 번호는 숫자여야 합니다.',
            'page.min' => '페이지 번호는 1 이상이어야 합니다.',

            'size.integer' => '페이지 크기(조회 개수)는 숫자여야 합니다.',
            'size.min' => '페이지 크기는 최소 1개 이상이어야 합니다.',
            'size.max' => '한 번에 최대 100개까지만 조회할 수 있습니다.',

            'sort.string' => '정렬 기준은 문자열이어야 합니다.',

            'region_id.integer' => '지역 ID는 숫자여야 합니다.',
            'region_id.exists' => '선택한 지역이 존재하지 않습니다.',
        ];
    }

    /**
     * service layer 에 전달할 정규환된 데이터
     * @return array{page:int, size:int, sort?:string, region_id?:int}
     */
    public function payload(): array
    {
        /** @var array{page?:int, size?:int, sort?:string, region_id?:int} $data */
        $data = $this->validated();

        return [
            'page' => $data['page'] ?? 1,
            'size' => $data['size'] ?? 10,
            'sort' => $data['sort'] ?? null,
            'region_id' => $data['region_id'] ?? null,
        ];
    }
    
}