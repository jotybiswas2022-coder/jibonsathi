<?php

namespace App\Http\Requests\Frontend\Discover;

use App\Services\DiscoveryService;
use App\Support\Reference;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:80'],
            'gender' => ['nullable', Rule::in(array_keys(Reference::genders()))],
            'age_from' => ['nullable', 'integer', 'between:18,80'],
            'age_to' => ['nullable', 'integer', 'between:18,90', 'gte:age_from'],
            'height_from' => ['nullable', 'integer', 'between:120,220'],
            'height_to' => ['nullable', 'integer', 'between:120,230', 'gte:height_from'],
            'marital_status' => ['nullable', Rule::in(array_keys(Reference::maritalStatuses()))],
            'religion' => ['nullable', Rule::in(array_keys(Reference::religions()))],
            'country' => ['nullable', Rule::in(array_keys(Reference::countries()))],
            'division' => ['nullable', Rule::in(Reference::divisionNames())],
            'district' => ['nullable', 'string', 'max:80'],
            'city' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:80'],
            'education' => ['nullable', Rule::in(array_keys(Reference::educationLevels()))],
            'profession' => ['nullable', 'string', 'max:80'],
            'income' => ['nullable', Rule::in(array_keys(Reference::incomeRanges()))],
            'diet' => ['nullable', Rule::in(array_keys(Reference::diets()))],
            'smoking' => ['nullable', Rule::in(array_keys(Reference::smoking()))],
            'drinking' => ['nullable', Rule::in(array_keys(Reference::drinking()))],
            'family_type' => ['nullable', Rule::in(array_keys(Reference::familyTypes()))],
            'verified' => ['nullable', 'boolean'],
            'apply_preference' => ['nullable', 'boolean'],
            'sort' => ['nullable', Rule::in(array_keys(DiscoveryService::SORTS))],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Only keep the filters that were actually supplied.
     *
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return collect($this->validated())
            ->reject(fn ($value, $key) => $key === 'page' || $value === null || $value === '' || $value === false)
            ->all();
    }
}
