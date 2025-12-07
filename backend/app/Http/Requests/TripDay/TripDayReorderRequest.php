<?php

namespace App\Http\Requests\TripDay;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TripDayReorderRequest extends FormRequest
{
    /**
     * 일정 주인만 접근 허용
     */
    public function authorize():bool
    {
        // URL에서 가져온 trip_id로 user_id 비교
        $tripId = $this->route('trip_id');
        $trip = \App\Models\Trip::findOrFail($tripId);

        // user_id와 로그인 사용자 일치일 경우 true
        return $this->user()->id === $trip->user_id;
    }

    /**
     * 일차 재배치 유효성검증
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string
     */
    public function rules(): array
    {
        // trip 파라미터에서 id 가져오기
        $tripId = $this->route("trip_id");
        
        return [
            "day_ids" => ['required', 'array', "min:1"],
            "day_ids.*" => ["required", "integer", "distinct", 
                                Rule::exists('trip_days', 'trip_day_id')
                                ->where('trip_id', $tripId)],
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
            'day_ids.array'    => 'day_ids 값은 배열 형식이어야 합니다.',
            'day_ids.min'      => '최소 1개 이상의 일차를 포함해야 합니다.',

            'day_ids.*.required' => '각 일차 ID 값은 필수입니다.',
            'day_ids.*.integer'  => '각 일차 ID 값은 숫자여야 합니다.',
            'day_ids.*.distinct' => 'day_ids 배열 안에 중복된 일차 ID가 존재합니다.',
            'day_ids.*.exists'   => '존재하지 않는 일차이거나, 현재 여행에 속하지 않는 일차 ID입니다.',
        ];
    }


    // /**
    //  * 일차 재배치 유효성검증
    //  * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
    //  */
    // public function rules(): array
    // {
    //     // trip 파라미터에서 id 가져오기
    //     $tripId = $this->route("trip_id");
        
    //     return[
    //         // 기존 일차 번호
    //         'old_day_no' => [
    //             'required',
    //             'integer',
    //             'min:1',
    //             Rule::exists('trip_days', 'day_no')
    //                 ->where('trip_id', $tripId)
    //         ],
    //         // 새 일차 번호
    //         'new_day_no' => [
    //             'required',
    //             'integer',
    //             'min:1',
    //             'different:old_day_no',
    //         ],
    //     ];
    // }
    
        // return [
        //     "orders" => ['required', 'array', "min:1"],
        //     "orders.*.day_no" => ["required", "integer", "distinct", 
        //                         Rule::exists('trip_days', 'day_no')->where('trip_id', $tripId)],
        //     "orders.*.new_day_no"=> ["required", "integer", "distinct",
        //                         Rule::exists('trip_days', 'day_no')->where('trip_id', $tripId)]
        // ];

    // /**
    //  * @return array{orders.*.day_no.distinct: string, orders.*.day_no.exists: string, orders.*.new_day_no.distinct: string, orders.*.new_day_no.exists: string, orders.required: string}
    //  */
    // public function messages(): array
    // {
    //     return [
    //         'old_day_no.required' => '변경할 기존 날짜(day_no)는 필수입니다.',
    //         'old_day_no.integer'  => '기존 날짜 값은 숫자여야 합니다.',
    //         'old_day_no.min'      => '기존 날짜 값은 1 이상이어야 합니다.',
    //         'old_day_no.exists'   => '존재하지 않는 일차(Day)이거나, 현재 여행에 속하지 않은 날짜입니다.',

    //         'new_day_no.required' => '이동할 목표 날짜(new_day_no)는 필수입니다.',
    //         'new_day_no.integer'  => '목표 날짜 값은 숫자여야 합니다.',
    //         'new_day_no.min'      => '목표 날짜 값은 1 이상이어야 합니다.',
    //         'new_day_no.different'=> '이동할 목표 날짜는 기존 날짜와 달라야 합니다.',
    //     ];

        // return [
        // 'orders.required' => '재배치할 날짜 정보는 필수입니다.',
        // 'orders.array'    => '날짜 정보는 배열 형식이어야 합니다.',
        // 'orders.min'      => '최소 1개 이상의 날짜를 재배치해야 합니다.',

        // 'orders.*.day_no.required' => '변경할 기존 날짜(day_no)는 필수입니다.',
        // 'orders.*.day_no.integer'  => '기존 날짜 값은 숫자여야 합니다.',
        // 'orders.*.day_no.distinct' => '동일한 날짜를 중복해서 선택할 수 없습니다.',
        // 'orders.*.day_no.exists'   => '존재하지 않는 일차(Day)이거나, 현재 여행에 속하지 않은 날짜입니다.',

        // 'orders.*.new_day_no.required' => '이동할 목표 날짜(new_day_no)는 필수입니다.',
        // 'orders.*.new_day_no.integer'  => '목표 날짜 값은 숫자여야 합니다.',
        // 'orders.*.new_day_no.distinct' => '이동할 목표 날짜가 중복될 수 없습니다.',
        // 'orders.*.new_day_no.exists'   => '이동하려는 날짜가 유효한 범위(일차) 밖입니다.',
        // ];
    // }
}