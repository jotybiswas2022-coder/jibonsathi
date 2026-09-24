<?php

namespace App\Policies;

use App\Models\User;

/**
 * Governs everything a member can do with another member: viewing, shortlisting,
 * messaging, reporting — plus the administrative actions on that member.
 */
class UserPolicy
{
    public function viewAny(User $viewer): bool
    {
        return $viewer->is_admin;
    }

    public function view(?User $viewer, User $target): bool
    {
        if ($viewer && $viewer->id === $target->id) {
            return true;
        }

        if ($viewer && $viewer->blocksWith($target)) {
            return false;
        }

        $profile = $target->profile;

        if (! $profile || ! $target->isActive()) {
            return $viewer?->is_admin ?? false;
        }

        return match ($profile->profile_visibility) {
            'public' => true,
            'members' => $viewer !== null,
            'private' => (bool) $viewer?->is_admin,
            default => $viewer !== null,
        };
    }

    public function update(User $viewer, User $target): bool
    {
        return $viewer->id === $target->id || $viewer->is_admin;
    }

    public function sendInterest(User $viewer, User $target): bool
    {
        return $viewer->canInteractWith($target);
    }

    public function shortlist(User $viewer, User $target): bool
    {
        return $viewer->id !== $target->id
            && ! $viewer->blocksWith($target)
            && $target->isActive();
    }

    public function message(User $viewer, User $target): bool
    {
        return $viewer->id !== $target->id
            && ! $viewer->blocksWith($target)
            && $viewer->isConnectedWith($target)
            && (bool) $target->profile?->allow_messages;
    }

    public function report(User $viewer, User $target): bool
    {
        return $viewer->id !== $target->id && ! $viewer->hasBlocked($target);
    }

    public function block(User $viewer, User $target): bool
    {
        return $viewer->id !== $target->id;
    }

    /* ------------------------- administration ------------------------- */

    public function manage(User $viewer): bool
    {
        return $viewer->is_admin;
    }

    public function verify(User $viewer, User $target): bool
    {
        return $viewer->is_admin;
    }

    public function suspend(User $viewer, User $target): bool
    {
        return $viewer->is_admin && $viewer->id !== $target->id && ! $target->is_admin;
    }

    public function activate(User $viewer, User $target): bool
    {
        return $viewer->is_admin && $viewer->id !== $target->id;
    }

    public function delete(User $viewer, User $target): bool
    {
        return $viewer->is_admin && $viewer->id !== $target->id && ! $target->is_admin;
    }
}
