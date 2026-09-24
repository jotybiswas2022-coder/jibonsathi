<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function view(User $viewer, Conversation $conversation): bool
    {
        return $conversation->hasParticipant($viewer->id);
    }

    public function send(User $viewer, Conversation $conversation): bool
    {
        if (! $conversation->hasParticipant($viewer->id)) {
            return false;
        }

        $partner = $conversation->partnerFor($viewer->id);

        return $partner === null || ! $viewer->blocksWith($partner);
    }

    public function delete(User $viewer, Conversation $conversation): bool
    {
        return $conversation->hasParticipant($viewer->id);
    }
}
