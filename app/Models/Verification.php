<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Verification extends Model
{
    public const TYPE_EMAIL = 'email';
    public const TYPE_PHONE = 'phone';
    public const TYPE_PROFILE = 'profile';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const TYPES = [
        self::TYPE_EMAIL => 'Email address',
        self::TYPE_PHONE => 'Phone number',
        self::TYPE_PROFILE => 'Profile identity',
    ];

    protected $fillable = [
        'user_id',
        'type',
        'status',
        'document_path',
        'document_type',
        'note',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst((string) $this->type);
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            default => 'warning',
        };
    }

    /**
     * Documents are private — never expose the raw path publicly.
     */
    public function documentUrl(): ?string
    {
        return $this->document_path ? Media::url($this->document_path) : null;
    }
}
