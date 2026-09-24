<?php

namespace App\Http\Requests\Frontend\Profile;

use App\Support\Reference;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LifestyleRequest extends FormRequest
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
            'diet' => ['required', Rule::in(array_keys(Reference::diets()))],
            'smoking' => ['required', Rule::in(array_keys(Reference::smoking()))],
            'drinking' => ['required', Rule::in(array_keys(Reference::drinking()))],
            'hobbies' => ['nullable', 'array', 'max:20'],
            'hobbies.*' => ['string', 'max:60'],
            'interests' => ['nullable', 'array', 'max:20'],
            'interests.*' => ['string', 'max:60'],
            'about_lifestyle' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
