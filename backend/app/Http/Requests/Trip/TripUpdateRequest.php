<?php
namespace App\Http\Requests\Trip;

use Illuminate\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TripUpdateRequest extends FormRequest
{
    /**
     * 로그인 사용자 접근 허용
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * 여행 수정 유효성 검증
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'min:1', 'max:100'],
            'region_id' => ['sometimes', 'integer', Rule::exists('regions', 'region_id')],
            'start_date' => ['sometimes', 'date_format:Y-m-d'],
            'end_date' => ['sometimes', 'date_format:Y-m-d'],
        ];
    }

    /**
     * 유효성검증 실패시 메시지
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.string' => '여행 제목은 문자열이어야 합니다.',
            'title.max' => '여행 제목은 100자를 초과할 수 없습니다.',

            'region_id.integer' => '지역 ID는 숫자여야 합니다.',
            'region_id.exists' => '선택한 지역이 존재하지 않습니다.',

            'start_date.date_format' => '여행 시작일 형식이 올바르지 않습니다. (예: YYYY-MM-DD)',
            'end_date.date_format' => '여행 종료일 형식이 올바르지 않습니다. (예: YYYY-MM-DD)',
        ];
    }

    public function withValidator(Validator $validator) : void
    {
        $validator->after(function ($validator) {
            $start = $this->input('start_date');
            $end = $this->input('end_date');

            if ($start !== null && $end !== null && $end < $start) {
                $validator->errors()->add('end_date', '여행 종료일은 시작일과 같거나 그 이후여야 합니다.');
            }
        });
    }

    /**
     * @return array{title?:string, region_id?:int, start_date?:string, end_date?:string}
     */
    public function payload(): array
    {
        /** @var array{title?:string, region_id?:int, start_date?:string, end_date?:string} $data */
        $data = $this->validated();

        return $data;
    }
}