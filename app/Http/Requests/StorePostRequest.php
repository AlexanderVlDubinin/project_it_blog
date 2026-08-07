<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'content' => ['required', 'string', 'min:20'],
            'is_published' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'remove_image' => ['sometimes', 'boolean'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'tags' => ['nullable', 'array', 'max:7'],
            'tags.*' => ['required', 'string', 'min:2', 'max:50', 'not_regex:/[^a-zA-Z0-9\s]/'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            'title.min' => 'The title must be at least 3 characters.',
            'title.max' => 'The title must be at most 255 characters.',
            'content.required' => 'The content field is required.',
            'content.min' => 'The content must be at least 20 characters.',
            'image.mimes' => 'The image must be a valid image file.',
            'tags.max' => 'You cannot select more than 7 tags for one post.',
            'tags.*' => 'The tag name must be at least 2 characters.',
            'tags.*.max' => 'The tag name must be at most 50 characters.',
            'tags.*.not_regex' => 'The tag name must contain only letters, numbers, and spaces.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_published' => $this->boolean('is_published'),
            'remove_image' => $this->boolean('remove_image'),
            'user_id' => auth()->id(),
        ]);
    }
}
