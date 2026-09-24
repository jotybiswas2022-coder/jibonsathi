<?php

namespace App\Policies;

use App\Models\SuccessStory;
use App\Models\User;

class SuccessStoryPolicy
{
    public function viewAny(?User $viewer): bool
    {
        return true;
    }

    public function view(?User $viewer, SuccessStory $story): bool
    {
        return $story->is_published || (bool) $viewer?->is_admin;
    }

    public function manage(User $viewer): bool
    {
        return $viewer->is_admin;
    }
}
