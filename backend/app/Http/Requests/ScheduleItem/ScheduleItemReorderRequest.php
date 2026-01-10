<?php
namespace App\Http\Requests\ScheduleItem;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ScheduleItemReorderRequest extends FormRequest
{
    /**
     * 일정 주인만 접근 허용
     */
    public function authorize()
    {
        return $this->user() !== null;
    }

    /**
     * 일정 아이템 순서 재배치 유효성검증
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // orders 전체
            'orders' => ['required', 'array', 'min:1'],

            // 각 요소의 trip_day_id
            'orders.*.trip_day_id' => ['required', 'integer', Rule::exists('trip_days', 'trip_day_id')],

            // 각 요소의 item_ids 배열
            'orders.*.item_ids' => ['required', 'array', 'min:1'],

            // 실제 ScheduleItem ID들
            'orders.*.item_ids.*' => ['required', 'integer', 'distinct', Rule::exists('schedule_items', 'schedule_item_id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    { 
        return [
            'orders.required' => '재배치할 순서 정보(orders)는 필수입니다.',
            'orders.array' => 'orders 필드는 배열 형식이어야 합니다.',
            'orders.min' => '최소 1개 이상의 재배치 정보가 필요합니다.',

            'orders.*.trip_day_id.required' => '각 항목의 trip_day_id 값은 필수입니다.',
            'orders.*.trip_day_id.integer' => 'trip_day_id 값은 숫자여야 합니다.',
            'orders.*.trip_day_id.exists' => '존재하지 않는 Trip Day ID가 포함되어 있습니다.',

            'orders.*.item_ids.required' => '각 trip_day_id에 대해 재배치할 item_ids 배열이 필요합니다.',
            'orders.*.item_ids.array' => 'item_ids는 배열 형식이어야 합니다.',
            'orders.*.item_ids.min' => '각 Trip Day에는 최소 1개 이상의 일정 아이템이 포함되어야 합니다.',

            'orders.*.item_ids.*.required' => '일정 아이템 ID는 필수입니다.',
            'orders.*.item_ids.*.integer' => '일정 아이템 ID는 숫자여야 합니다.',
            'orders.*.item_ids.*.distinct' => '동일한 일정 아이템 ID를 중복해서 보낼 수 없습니다.',
            'orders.*.item_ids.*.exists' => '존재하지 않는 일정 아이템 ID가 포함되어 있습니다.',
        ];
    }

    /**
     * @return array{orders: array<int, array{trip_day_id:int, item_ids: array<int, int>}>}
     */
    public function payload(): array
    {
        /** @var array{orders: array<int, array{trip_day_id:int, item_ids: array<int, int>}>} $data */
        $data = $this->validated();

        return $data;
    }
}