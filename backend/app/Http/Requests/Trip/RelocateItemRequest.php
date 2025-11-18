<?php

namespace App\Http\Requests\Trip;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RelocateItemRequest extends FormRequest
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
        $dayNO = $this->route("day");

        return [
            "orders" => ["required","array", "min:1"],
            "orders.*.item_id" => ["required","integer","distinct",
                                Rule::exists('schedule_items', 'schedule_item_id')->where('day_no', $dayNO)],
            "orders.*.new_seq_no"=> ["required","integer","distinct", "min:1"],
        ];
    }

    public function messages(): array
    {
        return [
            'orders.required'              => '재배치할 순서 정보가 필요합니다.',
            'orders.min'                   => '재배치할 아이템을 최소 1개 이상 포함해야 합니다.',
            
            'orders.*.item_id.exists'      => '유효하지 않거나 해당 일차에 속하지 않는 아이템입니다.',
            'orders.*.item_id.distinct'    => '동일한 아이템을 중복해서 재배치할 수 없습니다.',
            
            'orders.*.new_seq_no.required' => '아이템의 새로운 순서 번호는 필수입니다.',
            'orders.*.new_seq_no.min'      => '순서 번호는 1 이상이어야 합니다.',
        ];
    }
}
