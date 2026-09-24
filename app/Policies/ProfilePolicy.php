<?php

namespace App\Policies;

use App\Models\Profile;
use App\Models\User;

/**
 * Profile records (not members) — used by the admin moderation queue.
 */
class ProfilePolicy
{
    public function view(User $viewer, Profile $profile): bool
    {
        return $viewer->is_admin || $viewer->id === $profile->user_id;
    }

    public function moderate(User $viewer, Profile $profile): bool
    {
        return $viewer->is_admin;
    }

    public function approve(User $viewer, Profile $profile): bool
    {
        return $viewer->is_admin;
    }

    public function suspend(User $viewer, Profile $profile): bool
    {
        return $viewer->is_admin && $viewer->id !== $profile->user_id;
    }
}
