<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    protected $fillable = [
        'user_one_id',
        'user_two_id',
        'last_message_id',
        'last_message_at',
        'user_one_last_read_at',
        'user_two_last_read_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'user_one_last_read_at' => 'datetime',
            'user_two_last_read_at' => 'datetime',
        ];
    }

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->oldest();
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Resolve the participant that is not the given user.
     */
    public function partnerFor(int $userId): ?User
    {
        if ($this->user_one_id === $userId) {
            return $this->userTwo;
        }

        return $this->user_one_id === $userId ? $this->userOne : $this->userTwo;
    }

    public function hasParticipant(int $userId): bool
    {
        return $this->user_one_id === $userId || $this->user_two_id === $userId;
    }

    public function lastReadAtFor(int $userId): ?\Illuminate\Support\Carbon
    {
        return $this->user_one_id === $userId
            ? $this->user_one_last_read_at
            : $this->user_two_last_read_at;
    }

    public function unreadCountFor(int $userId): int
    {
        $lastRead = $this->lastReadAtFor($userId);

        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->when($lastRead, fn ($q) => $q->where('created_at', '>', $lastRead))
            ->when(! $lastRead, fn ($q) => $q->whereNull('read_at'))
            ->count();
    }

    public function scopeForUser(Builder $query, User|int $user): Builder
    {
        $id = $user instanceof User ? $user->id : $user;

        return $query->where(fn ($q) => $q->where('user_one_id', $id)->orWhere('user_two_id', $id));
    }

    /**
     * Locate (or create) the conversation between two users.
     */
    public static function between(int $firstId, int $secondId): self
    {
        [$one, $two] = $firstId < $secondId ? [$firstId, $secondId] : [$secondId, $firstId];

        return static::firstOrCreate([
            'user_one_id' => $one,
            'user_two_id' => $two,
        ]);
    }
}
