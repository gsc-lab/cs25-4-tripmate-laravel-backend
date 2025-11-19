<?php

namespace App\Http\Requests\Trip;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreItemRequest extends FormRequest
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
            'place_id' => ['required', 'integer', 'min:1', Rule::exists('places', 'place_id')],
            'seq_no' => ['required', 'integer', 'min:1'],
            'visit_time'=> ['sometimes', 'nullable', 'date_format:Y-m-d H:i']
        ];
    }
    
    public function messages(): array
    {
        return [
            'place_id.required' => '장소 ID는 필수입니다.',
            'place_id.integer'  => '장소 ID는 숫자여야 합니다.',
            'place_id.exists'   => '존재하지 않는 장소입니다.',
            'seq_no.integer'    => '순서는 숫자여야 합니다.',
            'visit_time.date_format' => '방문 시간 형식이 올바르지 않습니다.'
                ];
    }
}
