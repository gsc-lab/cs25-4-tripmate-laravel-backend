<?php
namespace App\Http\Requests\ScheduleItem;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleItemPatchRequest extends FormRequest
{
    /**
     * 일정 주인만 접근 허용
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * 일정 아이템 부분 수정(PATCH) 유효성 검증
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
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
            'visit_time.date_format' => '방문 시간 형식이 올바르지 않습니다. (예: HH:MM)',

            'memo.max' => '메모의 최대 글자 수는 255자 입니다.',
            'memo.string' => '메모는 문자열이어야 합니다.',
        ];
    }

    /**
     * @return array{visit_time?: ?string, memo?: ?string}
     */
    public function payload(): array
    {
        return $this->validated();
    }
}