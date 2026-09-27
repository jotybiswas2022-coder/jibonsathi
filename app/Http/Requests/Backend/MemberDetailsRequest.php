<?php

namespace App\Http\Requests\Backend;

use App\Http\Requests\Frontend\Profile\PhotoUploadRequest;
use App\Models\Profile;
use App\Support\Reference;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The one long form behind "create member" and "edit every detail" in the admin.
 *
 * Account fields are required; everything from the gender down to the partner
 * preference is optional, because a profile can be filled in over time and the
 * member may have told the admin nothing about their preferences yet.
 */
class MemberDetailsRequest extends FormRequest
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
        $userId = $this->route('user')?->id;

        return [
            // Account
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($userId)->whereNull('deleted_at')],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($userId)->whereNull('deleted_at')],
            'username' => ['nullable', 'string', 'max:60', Rule::unique('users', 'username')->ignore($userId)->whereNull('deleted_at')],
            'password' => [$this->isMethod('POST') ? 'required' : 'nullable', 'string', 'min:8', 'max:120', 'confirmed'],
            'status' => ['required', Rule::in(array_keys(Reference::userStatuses()))],
            'is_admin' => ['nullable', 'boolean'],

            // Basic
            'gender' => ['required', Rule::in(array_keys(Reference::genders()))],
            'date_of_birth' => ['nullable', 'date', 'before:today', 'after:1920-01-01'],
            'height_cm' => ['nullable', 'integer', 'min:120', 'max:220'],
            'marital_status' => ['nullable', Rule::in(array_keys(Reference::maritalStatuses()))],
            'religion' => ['nullable', Rule::in(array_keys(Reference::religions()))],
            'mother_tongue' => ['nullable', 'string', 'max:60'],
            'headline' => ['nullable', 'string', 'max:160'],
            'about_me' => ['nullable', 'string', 'max:1200'],

            // Moderation
            'profile_status' => ['required', Rule::in(array_keys(Profile::STATUSES))],
            'verification_status' => ['required', Rule::in(['unverified', 'pending', 'verified', 'rejected'])],

            // Location
            'country' => ['nullable', Rule::in(array_keys(Reference::countries()))],
            'division' => ['nullable', 'string', 'max:80'],
            'district' => ['nullable', 'string', 'max:80'],
            'city' => ['nullable', 'string', 'max:120'],

            // Education
            'level' => ['nullable', 'string', 'max:120'],
            'degree' => ['nullable', 'string', 'max:120'],
            'institution' => ['nullable', 'string', 'max:150'],
            'field_of_study' => ['nullable', 'string', 'max:120'],
            'start_year' => ['nullable', 'integer', 'min:1950', 'max:' . now()->year],
            'end_year' => ['nullable', 'integer', 'min:1950', 'max:' . now()->year],

            // Occupation
            'designation' => ['nullable', 'string', 'max:120'],
            'company' => ['nullable', 'string', 'max:150'],
            'employment_type' => ['nullable', 'string', 'max:60'],
            'income_range' => ['nullable', 'string', 'max:60'],
            'work_location' => ['nullable', 'string', 'max:120'],

            // Family
            'family_type' => ['nullable', 'string', 'max:60'],
            'family_status' => ['nullable', 'string', 'max:60'],
            'father_occupation' => ['nullable', 'string', 'max:120'],
            'mother_occupation' => ['nullable', 'string', 'max:120'],
            'brothers' => ['nullable', 'integer', 'min:0', 'max:20'],
            'sisters' => ['nullable', 'integer', 'min:0', 'max:20'],
            'family_income_range' => ['nullable', 'string', 'max:60'],
            'about_family' => ['nullable', 'string', 'max:1000'],

            // Lifestyle
            'diet' => ['nullable', 'string', 'max:60'],
            'smoking' => ['nullable', 'string', 'max:60'],
            'drinking' => ['nullable', 'string', 'max:60'],
            'hobbies' => ['nullable', 'array'],
            'hobbies.*' => ['nullable', 'string', 'max:40'],
            'interests' => ['nullable', 'array'],
            'interests.*' => ['nullable', 'string', 'max:40'],
            'about_lifestyle' => ['nullable', 'string', 'max:1000'],

            // Partner preference
            'preferred_gender' => ['nullable', Rule::in(array_keys(Reference::genders()))],
            'age_min' => ['nullable', 'integer', 'min:18', 'max:100'],
            'age_max' => ['nullable', 'integer', 'min:18', 'max:100', 'gte:age_min'],
            'height_min_cm' => ['nullable', 'integer', 'min:120', 'max:220'],
            'height_max_cm' => ['nullable', 'integer', 'min:120', 'max:220', 'gte:height_min_cm'],
            'preferred_country' => ['nullable', 'string', 'max:80'],
            'preferred_division' => ['nullable', 'string', 'max:80'],
            'preferred_district' => ['nullable', 'string', 'max:80'],
            'religions' => ['nullable', 'array'],
            'religions.*' => ['nullable', 'string', 'max:60'],
            'marital_statuses' => ['nullable', 'array'],
            'marital_statuses.*' => ['nullable', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:1000'],

            // Reuse the secure image rules but keep the file optional, so saving
            // the form does not force a fresh upload every single time.
            'photo' => array_values(array_filter(
                PhotoUploadRequest::photoRules(),
                static fn (string $rule): bool => $rule !== 'required'
            )),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'age_max.gte' => 'The maximum age cannot be below the minimum age.',
            'height_max_cm.gte' => 'The maximum height cannot be below the minimum height.',
            'profile_status.in' => 'That profile status does not exist.',
            'verification_status.in' => 'That verification status does not exist.',
        ];
    }

    /**
     * The validated payload in the shape the profile saver expects, with the
     * account-only fields dropped and the list fields left as arrays.
     *
     * @return array<string, mixed>
     */
    public function profilePayload(): array
    {
        return array_merge(
            $this->safe()->except(['password', 'password_confirmation', 'photo', 'is_admin', 'username']),
            [
                'hobbies' => $this->input('hobbies', []),
                'interests' => $this->input('interests', []),
                'religions' => $this->input('religions', []),
                'marital_statuses' => $this->input('marital_statuses', []),
            ]
        );
    }
}
