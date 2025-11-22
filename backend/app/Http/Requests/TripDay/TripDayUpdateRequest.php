<?php

namespace App\Http\Requests\TripDay;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TripDayUpdateRequest extends FormRequest
{
    /**
     * 로그인 사용자 접근 허용
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * 일차 수정 유효성검증
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'memo' => ['sometimes', 'nullable', 'string', 'max:255']
        ];
    }

    /**
     * @return array{memo.max: string, memo.string: string}
     */
    public function messages(): array
    {
        return [
            'memo.max' => '메모의 최대 글자 수는 255자 입니다.',
            'memo.string' => '메모는 문자열이어야 합니다.'
        ];
    }
}