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

        return [
            // 단일 일정 아이템 재배치 
            'item_id' => [
                'required',
                'integer',
                Rule::exists('schedule_items', 'schedule_item_id'),
            ],

            // 새로운 순서 번호
            'new_seq_no' => [
                'required',
                'integer',
                'min:1',
            ],
        ];
    }

    /**
     * @return array{orders.*.item_id.distinct: string, orders.*.item_id.exists: string, orders.*.new_seq_no.min: string, orders.*.new_seq_no.required: string, orders.min: string, orders.required: string}
     */
    public function messages(): array
    {
        return [
            'item_id.required' => '아이템 ID는 필수입니다.',
            'item_id.integer'  => '아이템 ID는 숫자여야 합니다.',
            'item_id.exists'   => '유효하지 않은 아이템 ID입니다.',

            'new_seq_no.required' => '새로운 순서 번호는 필수입니다.',
            'new_seq_no.integer'  => '순서 번호는 숫자여야 합니다.',
            'new_seq_no.min'      => '순서 번호는 1 이상이어야 합니다.',
        ];
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
    }
}