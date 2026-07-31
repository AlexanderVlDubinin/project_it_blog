<?php

namespace App\Http\Requests;

use App\Enum\CommentDeletionReason;
use App\Enum\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SoftDeleteCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return ($this->user() && in_array($this->user()->role, [UserRole::MODERATOR, UserRole::ADMIN]));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason_key' => ['required', Rule::enum(CommentDeletionReason::class)],
            'custom_reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
