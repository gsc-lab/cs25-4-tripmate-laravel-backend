<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class AuthVerificationRequest extends FormRequest
{
    /**
     * 로그인 사용자 접근 허용
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 회원탈퇴 유효성 검증
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password' => ['required'],
        ];
    }

    /**
     * 회원탈퇴 예외 메세지
     *
     * @return array{password.current_password: string, password.required: string}
     */
    public function messages(): array
    {
        return [
            'password.required' => '비밀번호를 입력해주세요.',
        ];
    }
}
