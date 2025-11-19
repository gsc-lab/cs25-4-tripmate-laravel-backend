<?php

namespace App\Http\Requests\Trip;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreDayRequest extends FormRequest
{
    /**
     * 로그인 사용자 접근 허용
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * 일차생성 유효성검증
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'day_no' => ['required', 'integer', 'min:1'],
            'memo' => ['nullable', 'string', 'max:255']
        ];
    }

    /**
     * @return array{day_no.integer: string, day_no.min: string, day_no.required: string, memo.max: string}
     */
    public function messages(): array
    {
        return [
            'day_no.required' => '일차 정보를 입력해주세요.',
            'day_no.integer'  => '일차는 숫자여야 합니다.',
            'day_no.min'      => '일차는 1 이상이어야 합니다.',
            'memo.max'        => '메모는 255자를 넘을 수 없습니다.'
        ];
    }
}
