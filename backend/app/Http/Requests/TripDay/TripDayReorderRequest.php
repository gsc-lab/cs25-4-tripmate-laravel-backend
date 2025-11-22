<?php

namespace App\Http\Requests\TripDay;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TripDayReorderRequest extends FormRequest
{
    /**
     * 로그인 사용자 접근 허용
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * 일차 재배치 유효성검증
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

    /**
     * @return array{orders.*.day_no.distinct: string, orders.*.day_no.exists: string, orders.*.new_day_no.distinct: string, orders.*.new_day_no.exists: string, orders.required: string}
     */
    public function messages(): array
    {
        return [
        'orders.required' => '재배치할 날짜 정보는 필수입니다.',
        'orders.array'    => '날짜 정보는 배열 형식이어야 합니다.',
        'orders.min'      => '최소 1개 이상의 날짜를 재배치해야 합니다.',

        'orders.*.day_no.required' => '변경할 기존 날짜(day_no)는 필수입니다.',
        'orders.*.day_no.integer'  => '기존 날짜 값은 숫자여야 합니다.',
        'orders.*.day_no.distinct' => '동일한 날짜를 중복해서 선택할 수 없습니다.',
        'orders.*.day_no.exists'   => '존재하지 않는 일차(Day)이거나, 현재 여행에 속하지 않은 날짜입니다.',

        'orders.*.new_day_no.required' => '이동할 목표 날짜(new_day_no)는 필수입니다.',
        'orders.*.new_day_no.integer'  => '목표 날짜 값은 숫자여야 합니다.',
        'orders.*.new_day_no.distinct' => '이동할 목표 날짜가 중복될 수 없습니다.',
        'orders.*.new_day_no.exists'   => '이동하려는 날짜가 유효한 범위(일차) 밖입니다.',
        ];
    }
}
