<?php

namespace App\Http\Requests\Frontend\Settings;

use App\Models\Profile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PrivacySettingsRequest extends FormRequest
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
            'profile_visibility' => ['required', Rule::in([
                Profile::VISIBILITY_PUBLIC,
                Profile::VISIBILITY_MEMBERS,
                Profile::VISIBILITY_PRIVATE,
            ])],
            'show_phone' => ['nullable', 'boolean'],
            'show_email' => ['nullable', 'boolean'],
            'allow_messages' => ['nullable', 'boolean'],
            'allow_profile_views' => ['nullable', 'boolean'],
            'show_online_status' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function privacyPayload(): array
    {
        return [
            'profile_visibility' => $this->validated('profile_visibility'),
            'show_phone' => $this->boolean('show_phone'),
            'show_email' => $this->boolean('show_email'),
            'allow_messages' => $this->boolean('allow_messages'),
            'allow_profile_views' => $this->boolean('allow_profile_views'),
            'show_online_status' => $this->boolean('show_online_status'),
        ];
    }
}
