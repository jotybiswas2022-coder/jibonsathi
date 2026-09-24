<?php

namespace App\Notifications;

class PhoneVerificationCodeNotification extends BaseNotification
{
    public function __construct(public string $code)
    {
    }

    protected function mailEnabled(): bool
    {
        return true;
    }

    public function content(): array
    {
        return [
            'title' => 'Your Jora verification code',
            'message' => "Use code {$this->code} to verify your phone number. It expires in 15 minutes.",
            'url' => route('verification.index', absolute: false),
            'tone' => 'info',
            'icon' => 'shield',
        ];
    }
}
