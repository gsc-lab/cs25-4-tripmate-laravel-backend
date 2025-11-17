<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "email"=> ['required', 'email', 'max:255'],
            'password'=> ['required', 'string'] 
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'email.required' => '이메일을 입력해주세요.',
            'email.email'    => '올바른 이메일 형식이 아닙니다.',
            'email.max'      => '이메일은 255자를 초과할 수 없습니다.',
            'password.required' => '비밀번호를 입력해주세요.',
            'password.string'   => '비밀번호는 문자열이어야 합니다.'
        ];
    }
}