<?php

namespace App\Notifications;

class VerificationRejectedNotification extends BaseNotification
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
        $message = 'Your '.str_replace('_', ' ', $this->type).' verification request was not approved.';

        if ($this->note) {
            $message .= ' Reason: '.$this->note;
        }

        return [
            'title' => 'Verification not approved',
            'message' => $message,
            'url' => route('verification.index', absolute: false),
            'tone' => 'danger',
            'icon' => 'shield',
        ];
    }
}
