<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; 

class PasswordConfirmRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password'=> ['required', 'current_password']
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => '비밀번호를 입력해주세요.',
            'password.current_password'   => '현재 비밀번호가 일치하지 않습니다.'
        ];
    }
}
