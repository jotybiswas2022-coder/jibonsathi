<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a persisted match/recommendation between two users.
 * Uses the `matches` table (the class cannot be named `Match`).
 */
class MatchRecord extends Model
{
    public const TYPE_RECOMMENDED = 'recommended';
    public const TYPE_NEW = 'new';
    public const TYPE_HIGH_COMPATIBILITY = 'high_compatibility';
    public const TYPE_MUTUAL = 'mutual';

    protected $table = 'matches';

    protected $fillable = [
        'user_id',
        'matched_user_id',
        'match_percentage',
        'reasons',
        'type',
        'is_mutual',
        'last_calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'reasons' => 'array',
            'is_mutual' => 'boolean',
            'match_percentage' => 'integer',
            'last_calculated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function matchedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'matched_user_id');
    }

    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        return $type ? $query->where('type', $type) : $query;
    }

    public function scopeHighCompatibility(Builder $query, int $min = 75): Builder
    {
        return $query->where('match_percentage', '>=', $min);
    }
}
