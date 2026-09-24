<?php

namespace App\Policies;

use App\Models\Interest;
use App\Models\User;

class InterestPolicy
{
    public function view(User $viewer, Interest $interest): bool
    {
        return $interest->sender_id === $viewer->id || $interest->receiver_id === $viewer->id;
    }

    public function respond(User $viewer, Interest $interest): bool
    {
        return $interest->receiver_id === $viewer->id && $interest->isPending();
    }

    public function cancel(User $viewer, Interest $interest): bool
    {
        return $interest->sender_id === $viewer->id && $interest->isPending();
    }
}
