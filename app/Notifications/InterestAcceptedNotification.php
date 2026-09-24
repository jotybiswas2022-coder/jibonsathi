<?php

namespace App\Notifications;

use App\Models\User;

class InterestAcceptedNotification extends BaseNotification
{
    public function __construct(public User $accepter)
    {
    }

    protected function preferenceKey(): ?string
    {
        return 'notify_interests';
    }

    public function content(): array
    {
        return [
            'title' => 'Interest accepted',
            'message' => "Great news — {$this->accepter->name} accepted your interest. You can start chatting now.",
            'url' => route('messages.index', absolute: false),
            'tone' => 'success',
            'icon' => 'check',
            'actor_id' => $this->accepter->id,
            'actor_name' => $this->accepter->name,
            'actor_photo' => $this->accepter->photoUrl(),
        ];
    }
}
