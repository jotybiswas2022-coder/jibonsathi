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

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_INVESTIGATING => 'Investigating',
            self::STATUS_RESOLVED => 'Resolved',
            self::STATUS_DISMISSED => 'Dismissed',
            default => ucfirst((string) $this->status),
        };
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

    /**
     * Whether the case still needs a decision. The tiles and the queue copy both
     * key off this, so "open" means the same thing everywhere.
     */
    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_INVESTIGATING], true);
    }

    /**
     * Reasons that can involve another member, so the queue can be read at a
     * glance. Everything else is about the account itself.
     */
    public function isSeriousReason(): bool
    {
        return in_array($this->reason, ['harassment', 'inappropriate_content', 'suspicious_activity'], true);
    }

    /**
     * How many reports this member has in total, including the current one.
     * The count is eager loaded, so a list of cases stays a fixed query count.
     */
    public function reportedUserReportTotal(): int
    {
        return (int) ($this->reportedUser?->reports_received_count ?? 0);
    }

    /**
     * A short excerpt of what the reporter wrote, for the list rows. Full text
     * belongs on the case page, where there is room to read it properly.
     */
    public function excerpt(int $limit = 120): string
    {
        $text = trim(preg_replace('/\s+/', ' ', (string) $this->description) ?? '');

        return mb_strlen($text) > $limit ? mb_substr($text, 0, $limit - 1).'…' : $text;
    }
}
