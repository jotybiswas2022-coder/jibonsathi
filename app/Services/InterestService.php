<?php

namespace App\Services;

use App\Models\Interest;
use App\Models\User;
use App\Notifications\InterestAcceptedNotification;
use App\Notifications\NewInterestNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InterestService
{
    public function __construct(private ConversationService $conversations)
    {
    }

    /**
     * Send an interest, guarding against duplicates and blocked parties.
     */
    public function send(User $sender, User $receiver): Interest
    {
        if ($sender->id === $receiver->id) {
            throw ValidationException::withMessages(['receiver' => 'You cannot send an interest to yourself.']);
        }

        if (! $receiver->isActive()) {
            throw ValidationException::withMessages(['receiver' => 'This profile is not accepting interests right now.']);
        }

        if ($sender->blocksWith($receiver)) {
            throw ValidationException::withMessages(['receiver' => 'You cannot interact with this profile.']);
        }

        $existing = Interest::query()
            ->where('sender_id', $sender->id)
            ->where('receiver_id', $receiver->id)
            ->first();

        if ($existing && $existing->status !== Interest::STATUS_CANCELLED) {
            throw ValidationException::withMessages(['receiver' => 'You already sent an interest to this profile.']);
        }

        $reverse = Interest::query()
            ->where('sender_id', $receiver->id)
            ->where('receiver_id', $sender->id)
            ->where('status', Interest::STATUS_PENDING)
            ->first();

        if ($reverse) {
            throw ValidationException::withMessages(['receiver' => 'This member already sent you an interest — accept it from your received list.']);
        }

        $interest = DB::transaction(function () use ($sender, $receiver, $existing) {
            if ($existing) {
                $existing->update(['status' => Interest::STATUS_PENDING, 'responded_at' => null]);

                return $existing->refresh();
            }

            return Interest::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'status' => Interest::STATUS_PENDING,
            ]);
        });

        $receiver->notify(new NewInterestNotification($sender));

        return $interest;
    }

    /**
     * Accept a received interest and open a conversation.
     */
    public function accept(Interest $interest, User $actor): Interest
    {
        $this->assertReceiver($interest, $actor);
        $this->assertPending($interest);

        DB::transaction(function () use ($interest) {
            $interest->update([
                'status' => Interest::STATUS_ACCEPTED,
                'responded_at' => now(),
            ]);
        });

        $this->conversations->start($interest->receiver, $interest->sender);

        $interest->sender->notify(new InterestAcceptedNotification($interest->receiver));

        return $interest->refresh();
    }

    public function reject(Interest $interest, User $actor): Interest
    {
        $this->assertReceiver($interest, $actor);
        $this->assertPending($interest);

        $interest->update([
            'status' => Interest::STATUS_REJECTED,
            'responded_at' => now(),
        ]);

        return $interest->refresh();
    }

    public function cancel(Interest $interest, User $actor): Interest
    {
        if ($interest->sender_id !== $actor->id) {
            throw ValidationException::withMessages(['interest' => 'You can only cancel interests you sent.']);
        }

        $this->assertPending($interest);

        $interest->update([
            'status' => Interest::STATUS_CANCELLED,
            'responded_at' => now(),
        ]);

        return $interest->refresh();
    }

    public function pendingCount(User $user): int
    {
        return $user->receivedInterests()->where('status', Interest::STATUS_PENDING)->count();
    }

    private function assertReceiver(Interest $interest, User $actor): void
    {
        if ($interest->receiver_id !== $actor->id) {
            throw ValidationException::withMessages(['interest' => 'Only the receiver can respond to this interest.']);
        }
    }

    private function assertPending(Interest $interest): void
    {
        if (! $interest->isPending()) {
            throw ValidationException::withMessages(['interest' => 'This interest has already been answered.']);
        }
    }
}
