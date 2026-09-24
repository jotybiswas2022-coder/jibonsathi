<?php

namespace App\Notifications;

class VerificationApprovedNotification extends BaseNotification
{
    public function __construct(public string $type = 'profile', public ?string $note = null)
    {
    }

    protected function mailEnabled(): bool
    {
        return true;
    }

    public function content(): array
    {
        return [
            'title' => 'Verification approved',
            'message' => 'Your '.str_replace('_', ' ', $this->type).' verification was approved. Your profile now shows a verified badge.',
            'url' => route('settings.profile', absolute: false),
            'tone' => 'success',
            'icon' => 'shield',
        ];
    }
}
