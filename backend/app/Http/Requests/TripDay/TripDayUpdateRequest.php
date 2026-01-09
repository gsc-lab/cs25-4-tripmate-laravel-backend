<?php

namespace App\Http\Requests\TripDay;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Trip;

class TripDayUpdateRequest extends FormRequest
{
    /**
     * 일정 주인만 접근 허용
     */
    public function authorize():bool
    {
        // URL에서 가져온 trip_id로 user_id 비교
        $tripId = $this->route('trip_id');
        $trip = Trip::findOrFail($tripId);

        // user_id와 로그인 사용자 일치일 경우 true
        return (int) $this->user()->getKey() === (int) $trip->user_id;
    }

    /**
     * 일차 수정 유효성검증
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'memo' => ['sometimes', 'nullable', 'string', 'max:255']
        ];
    }

    /**
     * @return array{memo.max: string, memo.string: string}
     */
    public function messages(): array
    {
        return [
            'memo.max' => '메모의 최대 글자 수는 255자 입니다.',
            'memo.string' => '메모는 문자열이어야 합니다.'
        ];
    }
}