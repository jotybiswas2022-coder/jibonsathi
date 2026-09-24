<?php

namespace App\Http\Requests\Frontend\Profile;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates one wizard step. Rule sets live on the dedicated step requests so
 * the registration wizard and the settings pages always stay in sync.
 */
class ProfileStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function step(): int
    {
        return (int) $this->route('step');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return match ($this->step()) {
            2 => BasicInfoRequest::stepRules(),
            3 => CareerRequest::stepRules(),
            4 => FamilyRequest::stepRules(),
            5 => LifestyleRequest::stepRules(),
            6 => PartnerPreferenceRequest::stepRules(),
            default => [],
        };
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date_of_birth.before' => 'You must be at least 18 years old to use Jora.',
            'age_max.gte' => 'The maximum age must be greater than the minimum age.',
            'height_max_cm.gte' => 'The maximum height must be greater than the minimum height.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->step() === 2 && $this->filled('district') && ! $this->filled('division')) {
            $division = collect(\App\Support\Reference::divisions())
                ->filter(fn (array $districts) => in_array($this->district, $districts, true))
                ->keys()
                ->first();

            if ($division) {
                $this->merge(['division' => $division]);
            }
        }
    }
}
