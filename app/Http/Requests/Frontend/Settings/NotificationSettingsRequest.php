<?php

namespace App\Http\Requests\Frontend\Settings;

use Illuminate\Foundation\Http\FormRequest;

class NotificationSettingsRequest extends FormRequest
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
            'notify_interests' => ['nullable', 'boolean'],
            'notify_messages' => ['nullable', 'boolean'],
            'notify_profile_views' => ['nullable', 'boolean'],
            'notify_shortlists' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, bool>
     */
    public function notificationPayload(): array
    {
        return [
            'notify_interests' => $this->boolean('notify_interests'),
            'notify_messages' => $this->boolean('notify_messages'),
            'notify_profile_views' => $this->boolean('notify_profile_views'),
            'notify_shortlists' => $this->boolean('notify_shortlists'),
        ];
    }
}
