<?php

namespace App\Http\Requests\Trip;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
        return [
            "orders" => ['required', 'array', "min:1"],
            "orders.*.day_no" => ["required", "integer", "distinct"],
            "orders.*.new_day_no"=> ["required", "integer", "distinct"]
        ];
    }

    public function messages(): array
    {
        return [

        ];
    }
}
