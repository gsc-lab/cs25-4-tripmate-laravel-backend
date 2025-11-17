<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class PasswordConfirmRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
            'password'=> ['required', 'string']
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => '비밀번호를 입력해주세요.',
            'password.string'   => '비밀번호는 문자열이어야 합니다.'
        ];
    }
}
