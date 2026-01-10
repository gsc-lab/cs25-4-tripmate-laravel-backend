<?php

namespace App\Http\Requests\ScheduleItem;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ScheduleItemStoreRequest extends FormRequest
{
    /**
     * 일정 주인만 접근 허용
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
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
            'visit_time' => ['sometimes', 'nullable', 'date_format:H:i'],
            'memo' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'place_id.required' => '장소 ID는 필수입니다.',
            'place_id.integer' => '장소 ID는 숫자여야 합니다.',
            'place_id.min' => '유효하지 않은 장소 ID입니다.',
            'place_id.exists' => '존재하지 않는 장소입니다. 장소 정보를 다시 확인해주세요.',

            'seq_no.required' => '순서(seq_no)는 필수입니다.',
            'seq_no.integer' => '순서는 숫자여야 합니다.',
            'seq_no.min' => '순서는 1 이상이어야 합니다.',

            'visit_time.date_format' => '방문 시간 형식이 올바르지 않습니다. (예: HH:MM)',

            'memo.max' => '메모의 최대 글자 수는 255자 입니다.',
            'memo.string' => '메모는 문자열이어야 합니다.',
        ];
    }

    /**
     * @return array{place_id:int, seq_no:int, visit_time:?string, memo:?string}
     */
    public function payload(): array
    {
        /** @var array{place_id:int, seq_no:int, visit_time?:string, memo?:string} $data */
        $data = $this->validated();

        $memo = $data['memo'] ?? null;
        if ($memo !== null) {
            $memo = trim($memo);
        }

        return [
            'place_id' => $data['place_id'],
            'seq_no' => $data['seq_no'],
            'visit_time' => $data['visit_time'] ?? null,
            'memo' => $memo,
        ];
    }
}