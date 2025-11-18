<?php

namespace App\Http\Requests\Place;

use Illuminate\Foundation\Http\FormRequest;

class SearchPlaceRequest extends FormRequest
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
            'place'     => ['sometimes', 'nullable', 'string'],
            'pageToken' => ['sometimes', 'nullable', 'string'], 
            'sort' => ['sometimes', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'place.string' => '장소이름은 문자열이여야 합니다.',
            'pageToken.integer' => '페이지는 문자열이어야 합니다.',
            'sort.string'  => '정렬 기준은 문자열이어야 합니다.',
        ];
    }
}
