<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_INVESTIGATING = 'investigating';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_DISMISSED = 'dismissed';

    public const REASONS = [
        'fake_profile' => 'Fake Profile',
        'spam' => 'Spam',
        'harassment' => 'Harassment',
        'inappropriate_content' => 'Inappropriate Content',
        'suspicious_activity' => 'Suspicious Activity',
        'other' => 'Other',
    ];

    protected $fillable = [
        'reporter_id',
        'reported_user_id',
        'reason',
        'description',
        'status',
        'admin_note',
        'handled_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function reasonLabel(): string
    {
        return self::REASONS[$this->reason] ?? ucfirst(str_replace('_', ' ', (string) $this->reason));
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            self::STATUS_RESOLVED => 'success',
            self::STATUS_INVESTIGATING => 'info',
            self::STATUS_DISMISSED => 'muted',
            default => 'warning',
        };
    }
}
