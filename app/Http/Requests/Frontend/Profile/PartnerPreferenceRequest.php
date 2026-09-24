<?php

namespace App\Http\Requests\Frontend\Profile;

use App\Support\Reference;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PartnerPreferenceRequest extends FormRequest
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
            'preferred_gender' => ['required', Rule::in(array_keys(Reference::genders()))],
            'age_min' => ['required', 'integer', 'between:18,70'],
            'age_max' => ['required', 'integer', 'between:18,80', 'gte:age_min'],
            'height_min_cm' => ['nullable', 'integer', 'between:120,220'],
            'height_max_cm' => ['nullable', 'integer', 'between:120,230', 'gte:height_min_cm'],
            'preferred_country' => ['nullable', Rule::in(array_keys(Reference::countries()))],
            'preferred_division' => ['nullable', Rule::in(Reference::divisionNames())],
            'preferred_district' => ['nullable', 'string', 'max:80'],
            'religions' => ['nullable', 'array'],
            'religions.*' => [Rule::in(array_keys(Reference::religions()))],
            'marital_statuses' => ['nullable', 'array'],
            'marital_statuses.*' => [Rule::in(array_keys(Reference::maritalStatuses()))],
            'education_level' => ['nullable', Rule::in(array_keys(Reference::educationLevels()))],
            'profession' => ['nullable', 'string', 'max:120'],
            'diet' => ['nullable', Rule::in(array_keys(Reference::diets()))],
            'smoking' => ['nullable', Rule::in(array_keys(Reference::smoking()))],
            'drinking' => ['nullable', Rule::in(array_keys(Reference::drinking()))],
            'notes' => ['nullable', 'string', 'max:600'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'age_max.gte' => 'The maximum age must be greater than the minimum age.',
            'height_max_cm.gte' => 'The maximum height must be greater than the minimum height.',
        ];
    }
}
