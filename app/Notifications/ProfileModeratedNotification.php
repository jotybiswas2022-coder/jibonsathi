<?php

namespace App\Notifications;

class ProfileModeratedNotification extends BaseNotification
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
        $approved = $this->status === 'approved';

        $message = $approved
            ? 'Your profile has been approved and is now visible to other members.'
            : 'Your profile was not approved in its current form.';

        if ($this->note) {
            $message .= ' Note: '.$this->note;
        }

        return [
            'title' => $approved ? 'Profile approved' : 'Profile needs changes',
            'message' => $message,
            'url' => route('settings.profile', absolute: false),
            'tone' => $approved ? 'success' : 'danger',
            'icon' => 'user',
        ];
    }
}
