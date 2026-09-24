<?php

namespace App\Notifications;

class AccountStatusNotification extends BaseNotification
{
    public function __construct(public string $status, public ?string $note = null)
    {
    }

    protected function mailEnabled(): bool
    {
        return true;
    }

    public function content(): array
    {
        $suspended = $this->status === 'suspended';

        $message = $suspended
            ? 'Your Jora account has been suspended by our moderation team.'
            : 'Your Jora account has been reactivated. Welcome back!';

        if ($this->note) {
            $message .= ' Note: '.$this->note;
        }

        return [
            'title' => $suspended ? 'Account suspended' : 'Account reactivated',
            'message' => $message,
            'url' => route('home', absolute: false),
            'tone' => $suspended ? 'danger' : 'success',
            'icon' => 'user',
        ];
    }
}
