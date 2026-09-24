<?php

namespace App\Notifications;

use App\Models\User;

class NewInterestNotification extends BaseNotification
{
    public function __construct(public User $sender)
    {
    }

    protected function preferenceKey(): ?string
    {
        return 'notify_interests';
    }

    public function content(): array
    {
        return [
            'title' => 'New interest received',
            'message' => "{$this->sender->name} sent you an interest and would like to connect.",
            'url' => route('interests.received', absolute: false),
            'tone' => 'primary',
            'icon' => 'heart',
            'actor_id' => $this->sender->id,
            'actor_name' => $this->sender->name,
            'actor_photo' => $this->sender->photoUrl(),
        ];
    }
}
