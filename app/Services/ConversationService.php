<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConversationService
{
    /**
     * Messaging requires a mutual connection (accepted interest) and no blocks.
     */
    public function start(User $first, User $second): Conversation
    {
        if ($first->id === $second->id) {
            throw ValidationException::withMessages(['conversation' => 'You cannot message yourself.']);
        }

        if ($first->blocksWith($second)) {
            throw ValidationException::withMessages(['conversation' => 'Messaging is unavailable with this profile.']);
        }

        if (! $first->isConnectedWith($second)) {
            throw ValidationException::withMessages([
                'conversation' => 'You can message a member once you are connected through a mutual interest.',
            ]);
        }

        if ($second->profile && ! $second->profile->allow_messages) {
            throw ValidationException::withMessages(['conversation' => 'This member is not accepting messages right now.']);
        }

        return Conversation::between($first->id, $second->id);
    }

    public function send(Conversation $conversation, User $sender, string $body): Message
    {
        if (! $conversation->hasParticipant($sender->id)) {
            throw ValidationException::withMessages(['message' => 'You are not part of this conversation.']);
        }

        $partner = $conversation->partnerFor($sender->id);

        if ($partner && $sender->blocksWith($partner)) {
            throw ValidationException::withMessages(['message' => 'Messaging is unavailable with this profile.']);
        }

        $message = DB::transaction(function () use ($conversation, $sender, $body) {
            $message = $conversation->messages()->create([
                'sender_id' => $sender->id,
                'body' => trim($body),
            ]);

            $readColumn = $conversation->user_one_id === $sender->id
                ? 'user_one_last_read_at'
                : 'user_two_last_read_at';

            $conversation->forceFill([
                'last_message_id' => $message->id,
                'last_message_at' => $message->created_at,
                $readColumn => $message->created_at,
            ])->save();

            return $message;
        });

        $partner?->notify(new NewMessageNotification($message));

        return $message;
    }

    /**
     * Mark every message the other member sent as read.
     */
    public function markRead(Conversation $conversation, User $reader): void
    {
        if (! $conversation->hasParticipant($reader->id)) {
            return;
        }

        $column = $conversation->user_one_id === $reader->id
            ? 'user_one_last_read_at'
            : 'user_two_last_read_at';

        $conversation->messages()
            ->where('sender_id', '!=', $reader->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $conversation->forceFill([$column => now()])->save();
    }

    /**
     * Remove the whole thread for both members.
     */
    public function delete(Conversation $conversation, User $actor): void
    {
        if (! $conversation->hasParticipant($actor->id)) {
            throw ValidationException::withMessages(['conversation' => 'You are not part of this conversation.']);
        }

        DB::transaction(function () use ($conversation) {
            $conversation->messages()->delete();
            $conversation->delete();
        });
    }

    public function unreadCount(User $user): int
    {
        $conversations = Conversation::query()
            ->forUser($user)
            ->with(['messages'])
            ->get();

        return $conversations->sum(fn (Conversation $c) => $c->unreadCountFor($user->id));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Conversation>
     */
    public function listFor(User $user, ?string $search = null)
    {
        return Conversation::query()
            ->forUser($user)
            ->with(['userOne.profile', 'userTwo.profile', 'latestMessage'])
            ->when($search, function ($query) use ($user, $search) {
                $query->whereHas('userOne', fn ($q) => $q->where('name', 'like', "%{$search}%")->where('id', '!=', $user->id))
                    ->orWhereHas('userTwo', fn ($q) => $q->where('name', 'like', "%{$search}%")->where('id', '!=', $user->id));
            })
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->get();
    }
}
