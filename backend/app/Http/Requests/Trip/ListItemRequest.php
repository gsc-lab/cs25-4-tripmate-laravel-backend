<?php

namespace App\Http\Requests\Trip;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ListItemRequest extends FormRequest
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
                'page' => ['sometimes', 'integer', 'min:1'],
                'size' => ['sometimes', 'integer', 'min:1', 'max:100'],
                'sort' => ['sometimes', 'string'],
            ];
    }

    public function messages(): array
    {
        return [
            'page.integer' => '페이지 번호는 숫자여야 합니다.',
            'size.integer' => '조회 개수는 숫자여야 합니다.',
            'size.max'     => '한 페이지당 최대 100개까지만 조회 가능합니다.',
            'sort.string'  => '정렬 값은 문자열이어야 합니다.',
        ];
    }
}
