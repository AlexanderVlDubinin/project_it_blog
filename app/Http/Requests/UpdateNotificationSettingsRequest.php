<?php

namespace App\Http\Requests;

use App\Enum\OldReadNotificationTerms;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) auth()->user(); // user must be authenticated
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Validate notifications TTL days
            'notifications_ttl_days' => ['required', Rule::enum(OldReadNotificationTerms::class)],
        ];
    }
}
