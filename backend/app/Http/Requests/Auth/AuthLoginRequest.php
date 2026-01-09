<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class AuthLoginRequest extends FormRequest
{
    /**
     * 사용자 접근 허용
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 로그인 유효성 검증
     * - email, password 검증
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * 로그인 예외 메세지
     */
    public function messages(): array
    {
        return [
            'email.required' => '이메일을 입력해주세요.',
            'email.email' => '올바른 이메일 형식이 아닙니다.',
            'email.max' => '이메일은 255자를 초과할 수 없습니다.',
            'password.required' => '비밀번호를 입력해주세요.',
            'password.string' => '비밀번호는 문자열이어야 합니다.',
        ];
    }
}
