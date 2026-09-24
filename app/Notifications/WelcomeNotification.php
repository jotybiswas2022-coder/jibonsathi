<?php

namespace App\Notifications;

class WelcomeNotification extends BaseNotification
{
    protected function mailEnabled(): bool
    {
        return true;
    }

    public function content(): array
    {
        return [
            'title' => 'Welcome to Jora',
            'message' => 'Your free account is ready. Complete your profile to start discovering meaningful matches.',
            'url' => route('dashboard', absolute: false),
            'tone' => 'primary',
            'icon' => 'sparkle',
        ];
    }
}
