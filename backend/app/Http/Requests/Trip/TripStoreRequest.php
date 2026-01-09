<?php

namespace App\Http\Requests\Trip;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TripStoreRequest extends FormRequest
{
    /**
     * 로그인 사용자 접근 허용
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * 여행 생성 유효성검증
     * - title, region_id, start_date, end_date 검증
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:1', 'max:100'],
            'region_id' => ['required', 'integer', Rule::exists('regions', 'region_id')],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ];
    }

    /**
     * 유효성검증 실패시 메시지
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => '여행 제목을 입력해주세요.',
            'title.string' => '여행 제목은 문자열이어야 합니다.',
            'title.max' => '여행 제목은 100자를 초과할 수 없습니다.',

            'region_id.required' => '지역을 선택해주세요.',
            'region_id.integer' => '지역 ID는 숫자여야 합니다.',
            'region_id.exists' => '선택한 지역이 존재하지 않습니다.',

            'start_date.required' => '여행 시작일을 입력해주세요.',
            'start_date.date_format' => '여행 시작일 형식이 올바르지 않습니다. (예: YYYY-MM-DD)',

            'end_date.required' => '여행 종료일을 입력해주세요.',
            'end_date.date_format' => '여행 종료일 형식이 올바르지 않습니다. (예: YYYY-MM-DD)',
            'end_date.after_or_equal' => '여행 종료일은 시작일과 같거나 그 이후여야 합니다.',
        ];
    }

    /**
     * service layer 에 전달할 정규환된 데이터
     * @return array{title:string, region_id:int, start_date:string, end_date:string}
     */
    public function payload(): array
    {
        /**
         * @var array{title:string, region_id:int, start_date:string, end_date:string} $data 
        */
        $data = $this->validated();

        $data['title'] = trim($data['title']);

        return $data;
    }
}