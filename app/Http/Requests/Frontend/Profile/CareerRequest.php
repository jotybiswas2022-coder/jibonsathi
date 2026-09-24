<?php

namespace App\Http\Requests\Frontend\Profile;

use App\Support\Reference;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CareerRequest extends FormRequest
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
        return self::stepRules();
    }

    /**
     * @return array<string, mixed>
     */
    public static function stepRules(): array
    {
        return [
            'education_level' => ['required', Rule::in(array_keys(Reference::educationLevels()))],
            'degree' => ['nullable', 'string', 'max:120'],
            'institution' => ['nullable', 'string', 'max:160'],
            'field_of_study' => ['nullable', 'string', 'max:120'],
            'end_year' => ['nullable', 'integer', 'between:1970,'.now()->year],
            'profession' => ['required', 'string', 'max:120'],
            'company' => ['nullable', 'string', 'max:160'],
            'employment_type' => ['nullable', Rule::in(array_keys(Reference::employmentTypes()))],
            'income_range' => ['nullable', Rule::in(array_keys(Reference::incomeRanges()))],
            'work_location' => ['nullable', 'string', 'max:120'],
        ];
    }
}
