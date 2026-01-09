<?php
namespace App\Http\Requests\TripDay;

use App\Models\Trip;
use Illuminate\Foundation\Http\FormRequest;

class TripDayUpdateRequest extends FormRequest
{
    /**
     * 일정 주인만 접근 허용
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * 일차 수정(메모) 유효성검증
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'memo' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'memo.max' => '메모의 최대 글자 수는 255자 입니다.',
            'memo.string' => '메모는 문자열이어야 합니다.',
        ];
    }

    /**
     * service에 전달할 정규화된 데이터
     * @return array{memo:?string}
     */ 
    public function payload(): array
    {
        /** @var array{memo?:string} $data */
        $data = $this->validated();

        $memo = $data['memo'] ?? null;

        if ($memo !== null) {
            $memo = trim($memo);
        }

        return ['memo' => $memo];
    }
}