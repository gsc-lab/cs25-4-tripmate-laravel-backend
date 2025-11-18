<?php

namespace App\Http\Requests\Trip;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RelocateDayRequest extends FormRequest
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
        // trip 파라미터에서 id 가져오기
        $trip = $this->route("trip");
        $tripId = $trip->trip_id;

        return [
            "orders" => ['required', 'array', "min:1"],
            "orders.*.day_no" => ["required", "integer", "distinct", 
                                Rule::exists('trip_days', 'day_no')->where('trip_id', $tripId)],
            "orders.*.new_day_no"=> ["required", "integer", "distinct",
                                Rule::exists('trip_days', 'day_no')->where('trip_id', $tripId)]
        ];
    }

    public function messages(): array
    {
        return [
            'orders.required' => '배열 값은 비워둘 수 없습니다.',
            'orders.*.day_no.exists' => '존재하지 않는 일차(Day)입니다.',
            'orders.*.day_no.distinct' => '동일한 날짜를 중복해서 재배치할 수 없습니다.',
            'orders.*.new_day_no.exists' => '이동하려는 날짜가 유효한 범위(일차) 밖입니다.',
            'orders.*.new_day_no.distinct' => '동일한 날짜를 중복해서 재배치할 수 없습니다.'
        ];
    }
}
