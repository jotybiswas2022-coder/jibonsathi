<?php

namespace App\Http\Requests\Frontend\Interaction;

use App\Models\Report;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportRequest extends FormRequest
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
            'reason' => ['required', Rule::in(array_keys(Report::REASONS))],
            'description' => ['nullable', 'string', 'max:1000', 'required_if:reason,other'],
            'block_user' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'description.required_if' => 'Please describe the issue when choosing "Other".',
        ];
    }
}
