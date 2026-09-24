<?php

namespace App\Http\Requests\Frontend\Verification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'document_type' => ['required', Rule::in(['national_id', 'passport', 'driving_license'])],
            'document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:5120'],
            'note' => ['nullable', 'string', 'max:400'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'document.mimes' => 'Upload a JPG, PNG, WEBP or PDF document.',
            'document.max' => 'Documents must be under 5 MB.',
        ];
    }
}
