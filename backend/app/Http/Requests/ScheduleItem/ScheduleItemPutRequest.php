<?php
namespace App\Http\Requests\ScheduleItem;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleItemPutRequest extends FormRequest
{
    /**
     * 일정 주인만 접근 허용
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * 일정 아이템 전체 수정(PUT) 유효성 검증
     * - visit_time, memo 둘 다 반드시 전달 (nullable 가능)
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'visit_time' => ['present', 'nullable', 'date_format:H:i'],
            'memo' => ['present', 'nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'visit_time.present' => '방문 시간(visit_time) 필드는 반드시 포함되어야 합니다.',
            'visit_time.date_format' => '방문 시간 형식이 올바르지 않습니다. (예: HH:MM)',
            'memo.present' => '메모(memo) 필드는 반드시 포함되어야 합니다.',
            'memo.string' => '메모(memo)는 문자열이어야 합니다.',
        ];
    }

    /**
     * @return array{visit_time: ?string, memo: ?string}
     */
    public function payload(): array
    {
        return $this->validated();
    }
}