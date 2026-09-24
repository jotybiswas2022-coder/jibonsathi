<?php

namespace App\Http\Requests\Frontend\Profile;

use App\Support\Reference;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FamilyRequest extends FormRequest
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
            'family_type' => ['required', Rule::in(array_keys(Reference::familyTypes()))],
            'family_status' => ['nullable', Rule::in(array_keys(Reference::familyStatuses()))],
            'father_occupation' => ['nullable', 'string', 'max:120'],
            'mother_occupation' => ['nullable', 'string', 'max:120'],
            'brothers' => ['nullable', 'integer', 'between:0,15'],
            'sisters' => ['nullable', 'integer', 'between:0,15'],
            'family_income_range' => ['nullable', Rule::in(array_keys(Reference::incomeRanges()))],
            'about_family' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
