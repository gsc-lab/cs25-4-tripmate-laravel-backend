<?php

namespace App\Http\Requests\ScheduleItem;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ScheduleItemReorderRequest extends FormRequest
{
    /**
     * 로그인 사용자 접근 허용
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * 일정 아이템 순서 재배치 유효성검증
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // $dayNO = $this->route("day");

        // return [
        //     "orders" => ["required","array", "min:1"],
        //     "orders.*.item_id" => ["required","integer","distinct",
        //                         Rule::exists('schedule_items', 'schedule_item_id')->where('day_no', $dayNO)],
        //     "orders.*.new_seq_no"=> ["required","integer","distinct", "min:1"],
        // ];

        // return [
        //     // 단일 일정 아이템 재배치 
        //     'item_id' => [
        //         'required',
        //         'integer',
        //         Rule::exists('schedule_items', 'schedule_item_id'),
        //     ],

        //     // 새로운 순서 번호
        //     'new_seq_no' => [
        //         'required',
        //         'integer',
        //         'min:1',
        //     ],
        // ];

        return [
            // orders 전체
            'orders' => [
                'required',
                'array',
                'min:1',
            ],

            // 각 요소의 trip_day_id
            'orders.*.trip_day_id' => [
                'required',
                'integer',
                Rule::exists('trip_days', 'trip_day_id'),
            ],

            // 각 요소의 item_ids 배열
            'orders.*.item_ids' => [
                'required',
                'array',
                'min:1',
            ],

            // 실제 ScheduleItem ID들
            'orders.*.item_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('schedule_items', 'schedule_item_id'),
            ],
        ];
    }

    /**
     * @return array{orders.*.item_id.distinct: string, orders.*.item_id.exists: string, orders.*.new_seq_no.min: string, orders.*.new_seq_no.required: string, orders.min: string, orders.required: string}
     */
    public function messages(): array
    {
        // return [
        //     'item_id.required' => '아이템 ID는 필수입니다.',
        //     'item_id.integer'  => '아이템 ID는 숫자여야 합니다.',
        //     'item_id.exists'   => '유효하지 않은 아이템 ID입니다.',

        //     'new_seq_no.required' => '새로운 순서 번호는 필수입니다.',
        //     'new_seq_no.integer'  => '순서 번호는 숫자여야 합니다.',
        //     'new_seq_no.min'      => '순서 번호는 1 이상이어야 합니다.',
        // ];
        // return [
        //     'orders.required' => '재배치할 순서 정보는 필수입니다.',
        //     'orders.array'    => '순서 정보는 배열 형식이어야 합니다.',
        //     'orders.min'      => '최소 1개 이상의 아이템을 재배치해야 합니다.',

        //     'orders.*.item_id.required' => '아이템 ID는 필수입니다.',
        //     'orders.*.item_id.integer'  => '아이템 ID는 숫자여야 합니다.',
        //     'orders.*.item_id.distinct' => '동일한 아이템을 중복해서 재배치할 수 없습니다.',
        //     'orders.*.item_id.exists'   => '유효하지 않거나 해당 일차에 속하지 않는 아이템입니다.',

        //     'orders.*.new_seq_no.required' => '새로운 순서 번호는 필수입니다.',
        //     'orders.*.new_seq_no.integer'  => '순서 번호는 숫자여야 합니다.',
        //     'orders.*.new_seq_no.distinct' => '순서 번호가 중복될 수 없습니다.',
        //     'orders.*.new_seq_no.min'      => '순서 번호는 1 이상이어야 합니다.',
        // ];

        return [
            'orders.required' => '재배치할 순서 정보(orders)는 필수입니다.',
            'orders.array'    => 'orders 필드는 배열 형식이어야 합니다.',
            'orders.min'      => '최소 1개 이상의 재배치 정보가 필요합니다.',

            'orders.*.trip_day_id.required' => '각 항목의 trip_day_id 값은 필수입니다.',
            'orders.*.trip_day_id.integer'  => 'trip_day_id 값은 숫자여야 합니다.',
            'orders.*.trip_day_id.exists'   => '존재하지 않는 Trip Day ID가 포함되어 있습니다.',

            'orders.*.item_ids.required' => '각 trip_day_id에 대해 재배치할 item_ids 배열이 필요합니다.',
            'orders.*.item_ids.array'    => 'item_ids는 배열 형식이어야 합니다.',
            'orders.*.item_ids.min'      => '각 Trip Day에는 최소 1개 이상의 일정 아이템이 포함되어야 합니다.',

            'orders.*.item_ids.*.required' => '일정 아이템 ID는 필수입니다.',
            'orders.*.item_ids.*.integer'  => '일정 아이템 ID는 숫자여야 합니다.',
            'orders.*.item_ids.*.distinct' => '동일한 일정 아이템 ID를 중복해서 보낼 수 없습니다.',
            'orders.*.item_ids.*.exists'   => '존재하지 않는 일정 아이템 ID가 포함되어 있습니다.',
        ];
    }
}