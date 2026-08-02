<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DateFiltersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'date_from' => ['nullable', 'date'], // 'date_format:Y-m-d'
            'date_to'   => ['nullable', 'date', 'after_or_equal:date_from'], // 'date_format:Y-m-d'
        ];
    }

    public function messages(): array
    {
        return [
            'q.max' => 'The Search string must be at most 255 characters.',
            'date_to.after_or_equal' => 'The "Date to" must be a date after or equal to "Date from".',
        ];
    }
}
