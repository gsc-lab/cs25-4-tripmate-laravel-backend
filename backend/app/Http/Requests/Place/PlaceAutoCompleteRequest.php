<?php

namespace App\Http\Requests\Place;

use Illuminate\Foundation\Http\FormRequest;

class PlaceAutoCompleteRequest extends FormRequest
{
    /**
     * 로그인 사용자 접근허용
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 자동 검색 입력값 유효성검증
     * - input(장소 검색), session_token(사용자 랜덤값)
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'input' => ['required', 'string', 'min:1'],
            'session_token' => ['required', 'nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'input.required' => '장소 값은 필수 값입니다.',
            'input.string' => '장소 값은 문자형이어야 합니다.',
            'input.min' => '장소 값은 최소 1자 이상이어야 합니다.',

            'session_token.required' => '세션 값은 필수 값입니다.',
            'session_token.string' => '세션 값은 문자형이어야 합니다.',
        ];
    }
}
