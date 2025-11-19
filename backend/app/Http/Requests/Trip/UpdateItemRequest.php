<?php

namespace App\Http\Requests\Trip;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateItemRequest extends FormRequest
{
    /**
     * 로그인 사용자 접근 허용
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * 일정 아이템 수정 유효성검증
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $item = $this->route("item");
        return [
            'visit_time' => ['sometimes', 'nullable', 'date_format:Y-m-d H:i'],
            'seq_no' => ['sometimes', 'integer', 'min:1'],
            'memo' => ['sometimes', 'nullable', 'string', 'max:255']
        ];
    }

    /**
     * @return array{memo.max: string, seq_no.integer: string, seq_no.min: string, visit_time.date_format: string}
     */
    public function messages(): array
    {
        return [
            'visit_time.date_format' => '방문 시간 형식이 올바르지 않습니다. (예: YYYY-MM-DD HH:MM)',
            'seq_no.integer' => '순서는 숫자여야 합니다.',
            'seq_no.min' => '아이템 순서는 반드시 1 이상의 정수값이어야 합니다.',
            'memo.max' => '메모는 255자를 넘을 수 없습니다.'
        ];
    }
}
