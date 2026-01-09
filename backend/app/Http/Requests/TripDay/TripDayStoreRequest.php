<?php

namespace App\Http\Requests\TripDay;

use App\Models\Trip;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TripDayStoreRequest extends FormRequest
{
    /**
     * 일정 주인만 접근 허용
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * 일차 생성 유효성검증
     * @return array<string, string>
     */
    public function rules(): array
    {
        $tripId = $this->route('trip_id');
        
        return [
            'day_no' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('trip_days', 'day_no')->where('trip_id', $tripId),
            ],
            'memo' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'day_no.required' => '일차(Day) 정보는 필수입니다.',
            'day_no.integer' => '일차는 숫자여야 합니다.',
            'day_no.min' => '일차는 1 이상이어야 합니다.',
            'day_no.unique' => '해당 여행에 이미 같은 일차가 존재합니다.',

            'memo.string' => '메모는 문자열이어야 합니다.',
            'memo.max' => '메모는 최대 255자까지 입력 가능합니다.',
        ];
    }

    /**
     * service에 전달할 정규화된 데이터
     * @return array{day_no:int, memo:?string}
     */
    public function payload(): array
    {
        /** @var array{day_no:int, memo?:string} $data */
        $data = $this->validated();

        // 메모가 존재하면 양쪽 공백 제거
        if (array_key_exists('memo', $data) && $data['memo'] !== null) {
            $data['memo'] = trim($data['memo']);
        }

        return $data;
    }
}