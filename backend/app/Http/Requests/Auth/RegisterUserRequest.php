<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class RegisterUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation()
    {
        // email의 필드 정규화
        if ($this->email) {
            // merge함수를 이용하여 덮어씌운다.
            $this->merge(['email' => Str::lower($this->email)]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "nickname" => ["required", 'string', 'max:50'],
            "email"=> ['required', 'email', 'max:255', Rule::unique('users', 'email_norm')],
            "password"=> ["required", Password::min(8)->letters()->numbers()->max(255)->symbols()] // 영문자, 숫자, 특수문자 포함
        ];
        }

    public function messages(): array
    {
        return [
            'nickname.required' => '닉네임을 입력해주세요.',
            'nickname.max'      => '닉네임은 50자를 초과할 수 없습니다.',
            
            'email.required' => '이메일을 입력해주세요.',
            'email.email'    => '올바른 이메일 형식이 아닙니다.',
            'email.max'      => '이메일은 255자를 초과할 수 없습니다.',
            'email.unique'   => '이미 사용 중인 이메일입니다.',
            
            'password.required'  => '비밀번호를 입력해주세요.'
        ];
    }
}
