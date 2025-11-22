<?php

namespace App\Http\Requests\ScheduleItem;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ScheduleItemStoreRequest extends FormRequest
{
    /**
     * 로그인 사용자 접근 허용
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * 일정 아이템 추가
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'place_id' => ['required', 'integer', 'min:1', Rule::exists('places', 'place_id')],
            'seq_no' => ['required', 'integer', 'min:1'],
            'visit_time'=> ['sometimes', 'nullable', 'date_format:Y-m-d H:i'],
            'memo' => ['sometimes', 'nullable', 'string', 'max:255']
        ];
    }
    
    /**
     * @return array{place_id.exists: string, place_id.integer: string, place_id.required: string, seq_no.integer: string, visit_time.date_format: string}
     */
    public function messages(): array
    {
        return [
            'place_id.required' => '장소 ID는 필수입니다.',
            'place_id.integer'  => '장소 ID는 숫자여야 합니다.',
            'place_id.min'      => '유효하지 않은 장소 ID입니다.',
            'place_id.exists'   => '존재하지 않는 장소입니다. 장소 정보를 다시 확인해주세요.',

            'seq_no.required'   => '순서(seq_no)는 필수입니다.',
            'seq_no.integer'    => '순서는 숫자여야 합니다.',
            'seq_no.min'        => '순서는 1 이상이어야 합니다.',

            'visit_time.date_format' => '방문 시간 형식이 올바르지 않습니다. (예: YYYY-MM-DD HH:MM)',

            'memo.max' => '메모의 최대 글자 수는 255자 입니다.',
            'memo.string' => '메모는 문자열이어야 합니다.'
        ];
    }
}
