<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthRegisterRequest extends FormRequest
{
    /**
     * 사용자 접근 허용
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 이메일 전처리
     * - 입력된 이메일을 바로 DB에 저장할 수 있도록 전처리 진행.
     *
     * @return void
     */
    public function prepareForValidation()
    {
        // email의 필드 정규화
        if ($this->email) {
            // merge함수를 이용하여 정규화된 이메일로 덮어씌운다.
            $this->merge(['email_norm' => Str::lower($this->email)]);
        }
    }

    /**
     * 회원가입 유효성 검증
     * - name, email_norm, password 검증
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'email_norm' => ['required', 'email', 'max:255', Rule::unique('users', 'email_norm')],
            'password' => ['required', Password::min(8)->letters()->numbers()->max(255)->symbols()], // 영문자, 숫자, 특수문자 포함
        ];
    }

    /**
     * 회원가입 예외 메세지
     *
     * @return array{email.email: string, email.max: string, email.required: string, email.unique: string, nickname.max: string, nickname.required: string, nickname.string: string, password.*: string, password.required: string}
     */
    public function messages(): array
    {
        return [
            'name.required' => '닉네임을 입력해주세요.',
            'name.string' => '닉네임은 문자형이어야 합니다.',
            'name.max' => '닉네임은 50자를 초과할 수 없습니다.',

            'email_norm.required' => '이메일을 입력해주세요.',
            'email_norm.email' => '올바른 이메일 형식이 아닙니다.',
            'email_norm.max' => '이메일은 255자를 초과할 수 없습니다.',
            'email_norm.unique' => '이미 사용 중인 이메일입니다.',

            'password.required' => '비밀번호를 입력해주세요.',
            'password.*' => '비밀번호는 8~255자 사이의 영문자, 특수문자, 숫자로 구성되어야합니다.',
        ];
    }
}
