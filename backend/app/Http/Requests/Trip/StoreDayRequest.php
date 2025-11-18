<?php

namespace App\Http\Requests\Trip;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreDayRequest extends FormRequest
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
            'day_no' => ['required', 'integer', 'min:1'],
            'memo' => ['nullable', 'string', 'max:255']
        ];
    }

    public function messages(): array
    {
        return [
            'day_no.required' => '일차 정보를 입력해주세요.',
            'day_no.integer'  => '일차는 숫자여야 합니다.',
            'day_no.min'      => '일차는 1 이상이어야 합니다.',
            'memo.max'        => '메모는 255자를 넘을 수 없습니다.'
        ];
    }
}
