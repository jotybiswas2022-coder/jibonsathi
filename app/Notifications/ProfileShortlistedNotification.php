<?php

namespace App\Notifications;

use App\Models\User;

class ProfileShortlistedNotification extends BaseNotification
{
    public function __construct(public User $shortlister)
    {
    }

    protected function preferenceKey(): ?string
    {
        return 'notify_shortlists';
    }

    public function content(): array
    {
        return [
            'title' => 'You were shortlisted',
            'message' => "{$this->shortlister->name} added your profile to their shortlist.",
            'url' => route('profiles.show', $this->shortlister->username, absolute: false),
            'tone' => 'accent',
            'icon' => 'star',
            'actor_id' => $this->shortlister->id,
            'actor_name' => $this->shortlister->name,
            'actor_photo' => $this->shortlister->photoUrl(),
        ];
    }
}
