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

    /** The same three, short enough for a status chip or a filter hint. */
    public const TYPES_SHORT = [
        self::TYPE_EMAIL => 'Email',
        self::TYPE_PHONE => 'Phone',
        self::TYPE_PROFILE => 'Profile',
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

    /**
     * A short label for the tiles and the table. The long form above reads well in
     * a sentence but is too long for a status chip.
     */
    public function typeShortLabel(): string
    {
        return self::TYPES_SHORT[$this->type] ?? ucfirst((string) $this->type);
    }

    /**
     * An icon per type, so the same three glyphs are used by the tiles, the chips
     * and the rows instead of each view picking its own.
     */
    public function typeIcon(): string
    {
        return match ($this->type) {
            self::TYPE_EMAIL => 'fa-envelope',
            self::TYPE_PHONE => 'fa-mobile-screen',
            self::TYPE_PROFILE => 'fa-id-card',
            default => 'fa-file-shield',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            default => ucfirst((string) $this->status),
        };
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            default => 'warning',
        };
    }

    public function statusIcon(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'fa-circle-check',
            self::STATUS_REJECTED => 'fa-ban',
            default => 'fa-hourglass-half',
        };
    }

    /**
     * Whether the case is still waiting on an admin. The copy on both pages keys
     * off this, so "open" means the same thing everywhere.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * The file extension, upper cased, or null when nothing was uploaded. A
     * verification with no file is a normal thing to find in the queue, so both
     * the row and the case page say so rather than showing a broken preview.
     */
    public function documentExtension(): ?string
    {
        if (! $this->document_path) {
            return null;
        }

        $ext = strtoupper(pathinfo((string) $this->document_path, PATHINFO_EXTENSION));

        return $ext === '' ? null : $ext;
    }

    public function hasDocument(): bool
    {
        return filled($this->document_path);
    }

    /**
     * Documents are private — never expose the raw path publicly.
     */
    public function documentUrl(): ?string
    {
        return $this->document_path ? Media::url($this->document_path) : null;
    }
}
