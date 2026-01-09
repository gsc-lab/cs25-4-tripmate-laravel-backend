<?php
namespace App\Http\Requests\TripDay;

use App\Models\Trip;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TripDayReorderRequest extends FormRequest
{
    /**
     * 일정 주인만 접근 허용
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * 일차 재배치 유효성검증
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // trip 파라미터에서 id 가져오기
        $tripId = $this->route('trip_id');

        return [
            'day_ids' => ['required', 'array', 'min:1'],
            'day_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('trip_days', 'trip_day_id')->where('trip_id', $tripId),
            ],
        ];
    }

    /**
     * 유효성 검사 메시지
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'day_ids.required' => '재배치할 일차 ID 목록(day_ids)은 필수입니다.',
            'day_ids.array' => 'day_ids 값은 배열 형식이어야 합니다.',
            'day_ids.min' => '최소 1개 이상의 일차를 포함해야 합니다.',

            'day_ids.*.required' => '각 일차 ID 값은 필수입니다.',
            'day_ids.*.integer' => '각 일차 ID 값은 숫자여야 합니다.',
            'day_ids.*.distinct' => 'day_ids 배열 안에 중복된 일차 ID가 존재합니다.',
            'day_ids.*.exists' => '존재하지 않는 일차이거나, 현재 여행에 속하지 않는 일차 ID입니다.',
        ];
    }

    /**
     * service에 전달할 정규화된 데이터
     * @return array{day_ids:int[]}
     */
    public function payload(): array
    {
        /** @var array{day_ids:int[]} $data */
        $data = $this->validated();

        return [
            'day_ids' => array_values(array_map('intval', $data['day_ids'])),
        ];
    }
}