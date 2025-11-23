<?php

namespace App\Http\Requests\TripDay;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TripDaystoreRequest extends FormRequest
{
    /**
     * 로그인 사용자 접근 허용
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * 일차 생성 유효성검증
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'day_no' => ['required', 'integer', 'min:1'],
            'memo' => ['sometimes', 'nullable', 'string', 'max:255']
        ];
    }

    /**
     * @return array{day_no.integer: string, day_no.min: string, day_no.required: string, memo.max: string}
     */
    public function messages(): array
    {
        return [
            'day_no.required' => '일차(Day) 정보는 필수입니다.',
            'day_no.integer'  => '일차는 숫자여야 합니다.',
            'day_no.min'      => '일차는 1 이상이어야 합니다.',

            'memo.string'     => '메모는 문자열이어야 합니다.',
            'memo.max'        => '메모는 최대 255자까지 입력 가능합니다.',
        ];
    }
}
