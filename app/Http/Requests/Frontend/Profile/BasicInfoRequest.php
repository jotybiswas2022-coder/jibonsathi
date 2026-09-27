<?php

namespace App\Http\Requests\Frontend\Profile;

use App\Support\Reference;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BasicInfoRequest extends FormRequest
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
        return self::stepRules($this->input('country'));
    }

    /**
     * @return array<string, mixed>
     */
    public static function stepRules(?string $country = null): array
    {
        return [
            'gender' => ['required', Rule::in(array_keys(Reference::genders()))],
            'date_of_birth' => ['required', 'date', 'before:-18 years', 'after:-80 years'],
            'height_cm' => ['required', 'integer', 'between:120,220'],
            'marital_status' => ['required', Rule::in(array_keys(Reference::maritalStatuses()))],
            'religion' => ['required', Rule::in(array_keys(Reference::religions()))],
            'mother_tongue' => ['nullable', 'string', 'max:60'],
            'country' => ['required', Rule::in(array_keys(Reference::countries()))],
            // Bangladesh picks one of the eight divisions from a list. Everywhere
            // else the same answer is a state or province the member types, so it
            // is only checked for being present and short.
            'division' => Reference::usesDivisions($country)
                ? ['required', Rule::in(Reference::divisionNames())]
                : ['required', 'string', 'max:80'],
            'district' => ['required', 'string', 'max:80'],
            'city' => ['nullable', 'string', 'max:120'],
            'headline' => ['nullable', 'string', 'max:160'],
            'about_me' => ['nullable', 'string', 'max:1200'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date_of_birth.before' => 'You must be at least 18 years old to use Jibon Sathi.',
            'date_of_birth.after' => 'Please enter a valid date of birth.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Only Bangladesh has districts that can be traced back to a division.
        if (! Reference::usesDivisions($this->input('country'))) {
            return;
        }

        if ($this->filled('district') && ! $this->filled('division')) {
            $division = collect(Reference::divisions())
                ->filter(fn (array $districts) => in_array($this->district, $districts, true))
                ->keys()
                ->first();

            if ($division) {
                $this->merge(['division' => $division]);
            }
        }
    }
}
