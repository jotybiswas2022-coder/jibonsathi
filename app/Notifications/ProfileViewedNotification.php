<?php

namespace App\Notifications;

use App\Models\User;

class ProfileViewedNotification extends BaseNotification
{
    public function __construct(public User $viewer)
    {
    }

    protected function preferenceKey(): ?string
    {
        return 'notify_profile_views';
    }

    public function content(): array
    {
        return [
            'title' => 'Your profile was viewed',
            'message' => "{$this->viewer->name} viewed your profile.",
            'url' => route('profiles.show', $this->viewer->username, absolute: false),
            'tone' => 'info',
            'icon' => 'eye',
            'actor_id' => $this->viewer->id,
            'actor_name' => $this->viewer->name,
            'actor_photo' => $this->viewer->photoUrl(),
        ];
    }
}
