<?php

namespace App\Http\Requests\Backend;

use App\Models\Report;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in([
                Report::STATUS_INVESTIGATING,
                Report::STATUS_RESOLVED,
                Report::STATUS_DISMISSED,
                'suspend',
            ])],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
