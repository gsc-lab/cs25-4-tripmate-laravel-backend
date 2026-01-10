<?php

namespace App\Http\Requests\ScheduleItem;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleItemIndexRequest extends FormRequest
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
     * - page, size
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'size' => ['sometimes', 'integer', 'min:1', 'max:100'],
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
        ];
    }

    /**
     * @return array{page:int, size:int}
     */
    public function payload(): array
    {
        /** @var array{page?:int, size?:int} $data */
        $data = $this->validated();

        return [
            'page' => $data['page'] ?? 1,
            'size' => $data['size'] ?? 20,
        ];
    }
}