<?php

namespace App\Http\Requests\Trip;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TripUpdateRequest extends FormRequest
{
    /**
     * 로그인 사용자 접근 허용
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * 여행 수정 유효성 검증
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'min:1', 'max:100'],
            'region_id' => ['sometimes', 'integer', Rule::exists('regions', 'region_id')],
            'start_date' => ['sometimes', 'date_format:Y-m-d'],
            'end_date' => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:start_date']
        ];
    }

    public function messages(): array
    {
        return [
            'title.string'   => '여행 제목은 문자열이어야 합니다.',
            'title.max'      => '여행 제목은 100자를 초과할 수 없습니다.',

            'region_id.integer'  => '지역 ID는 숫자여야 합니다.',
            'region_id.exists'   => '선택한 지역이 존재하지 않습니다.',

            'start_date.date_format' => '여행 시작일 형식이 올바르지 않습니다. (예: YYYY-MM-DD)',

            'end_date.date_format'    => '여행 종료일 형식이 올바르지 않습니다. (예: YYYY-MM-DD)',
            'end_date.after_or_equal' => '여행 종료일은 시작일과 같거나 그 이후여야 합니다.'
        ];
    }
}
