<?php

namespace App\Http\Requests\TripDay;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TripDayIndexRequest extends FormRequest
{
    /**
     * 로그인 사용자 접근 허용
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * 일정 아이템 목록 유효성 검증
     * - page, size, sort 검증
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'size' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort' => ['sometimes', 'string', Rule::in(['latest', 'oldest'])],
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
            'sort.in' => '정렬 기준은 latest 또는 oldest만 허용됩니다.',
        ];
    }

    /**
     * sevice에 전달할 정규화된 데이터
     * @return array{page:int, size:int, sort:?string}
     */ 
    public function payload(): array
    {
        /** @var array{page?:int, size?:int, sort?:string} $data */
        $data = $this->validated();

        return [
            'page' => $data['page'] ?? 1,
            'size' => $data['size'] ?? 20,
            'sort' => $data['sort'] ?? null,
        ];
    }
}