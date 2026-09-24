<?php

namespace App\Notifications;

use App\Models\Verification;

class VerificationSubmittedNotification extends BaseNotification
{
    public function __construct(public Verification $verification)
    {
    }

    public function content(): array
    {
        $user = $this->verification->user;

        return [
            'title' => 'New verification request',
            'message' => "{$user?->name} submitted a {$this->verification->typeLabel()} verification request.",
            'url' => route('backend.verifications.show', $this->verification, absolute: false),
            'tone' => 'accent',
            'icon' => 'shield',
        ];
    }
}
