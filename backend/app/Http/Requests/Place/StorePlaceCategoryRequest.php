<?php

namespace App\Http\Requests\Place;

use Illuminate\Foundation\Http\FormRequest;

class StorePlaceCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return True;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'name' => ['required', 'string'],
                'category' => ['required', 'string'],
                'address' => ['required', 'string'],
                'external_ref' => ['required', 'string'],
                'lat' => ['required', 'numeric', 'between:-90,90'],
                'lng' => ['required', 'numeric', 'between:-180,180']
        ];
    }

    public function messages(): array
{
        return [
            'name.required'     => '장소 이름을 입력해주세요.',
            'name.string'       => '장소 이름은 문자열이어야 합니다.', 
            
            'category.required' => '카테고리를 입력해주세요.',
            'category.string'   => '카테고리는 문자열이어야 합니다.',
            
            'address.required'  => '주소를 입력해주세요.',
            
            'lat.required'      => '위도(lat) 값은 필수입니다.',
            'lat.numeric'       => '위도 값은 숫자여야 합니다.',
            'lat.between'       => '위도는 -90에서 90 사이여야 합니다.',

            'lng.required'      => '경도(lng) 값은 필수입니다.',
            'lng.numeric'       => '경도 값은 숫자여야 합니다.',
            'lng.between'       => '경도는 -180에서 180 사이여야 합니다.',
        ];
    }
}
