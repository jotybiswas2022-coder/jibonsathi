<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const VISIBILITY_PUBLIC = 'public';
    public const VISIBILITY_MEMBERS = 'members';
    public const VISIBILITY_PRIVATE = 'private';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_SUSPENDED = 'suspended';

    public const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_SUSPENDED => 'Suspended',
    ];

    /** The completion bands the queue is filtered by, weakest first. */
    public const COMPLETION_LOW = 60;

    public const COMPLETION_HIGH = 90;

    public const COMPLETION_BANDS = [
        'low' => 'Under 60%',
        'high' => '90% and over',
    ];

    protected $fillable = [
        'user_id',
        'gender',
        'date_of_birth',
        'height_cm',
        'marital_status',
        'religion',
        'mother_tongue',
        'country',
        'division',
        'district',
        'city',
        'about_me',
        'headline',
        'profile_completion',
        'profile_status',
        'verification_status',
        'approved_at',
        'profile_visibility',
        'show_phone',
        'show_email',
        'allow_messages',
        'allow_profile_views',
        'show_online_status',
        'notify_interests',
        'notify_messages',
        'notify_profile_views',
        'notify_shortlists',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'approved_at' => 'datetime',
            'show_phone' => 'boolean',
            'show_email' => 'boolean',
            'allow_messages' => 'boolean',
            'allow_profile_views' => 'boolean',
            'show_online_status' => 'boolean',
            'notify_interests' => 'boolean',
            'notify_messages' => 'boolean',
            'notify_profile_views' => 'boolean',
            'notify_shortlists' => 'boolean',
            'height_cm' => 'integer',
            'profile_completion' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ProfilePhoto::class, 'user_id', 'user_id');
    }

    public function age(): ?int
    {
        return $this->date_of_birth?->age;
    }

    public function ageGroup(): string
    {
        $age = $this->age();

        return $age ? "{$age} yrs" : 'Age not set';
    }

    public function heightLabel(): string
    {
        if (! $this->height_cm) {
            return 'Height not set';
        }

        $feet = intdiv($this->height_cm, 30.48);
        $inches = (int) round(($this->height_cm / 2.54) - ($feet * 12));
        $inches = min($inches, 11);

        return "{$feet}'{$inches}\" ({$this->height_cm} cm)";
    }

    public function locationLabel(): string
    {
        $parts = array_filter([$this->city, $this->district, $this->division, $this->country]);

        return $parts ? implode(', ', array_slice($parts, 0, 3)) : 'Location not set';
    }

    public function isApproved(): bool
    {
        return $this->profile_status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->profile_status === 'pending';
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->profile_status] ?? ucfirst((string) $this->profile_status);
    }

    public function statusTone(): string
    {
        return match ($this->profile_status) {
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_SUSPENDED => 'danger',
            default => 'warning',
        };
    }

    public function statusIcon(): string
    {
        return match ($this->profile_status) {
            self::STATUS_APPROVED => 'fa-circle-check',
            self::STATUS_REJECTED => 'fa-xmark',
            self::STATUS_SUSPENDED => 'fa-ban',
            default => 'fa-hourglass-half',
        };
    }

    /**
     * Whether the profile is still waiting on a decision. Both the queue copy and
     * the case page key off this, so "open" means the same thing everywhere.
     */
    public function isOpen(): bool
    {
        return $this->profile_status === self::STATUS_PENDING;
    }

    public function verificationLabel(): string
    {
        return match ($this->verification_status) {
            'verified' => 'Verified',
            'pending' => 'In review',
            'rejected' => 'Refused',
            default => 'Not checked',
        };
    }

    public function verificationTone(): string
    {
        return match ($this->verification_status) {
            'verified' => 'success',
            'rejected' => 'danger',
            'pending' => 'warning',
            default => 'muted',
        };
    }

    /**
     * A thin profile is the strongest signal in a moderation queue, because a
     * member who has not filled anything in has nothing to judge. The tone
     * follows the same bands the filter chips use.
     */
    public function completionTone(): string
    {
        $pct = (int) $this->profile_completion;

        return match (true) {
            $pct < self::COMPLETION_LOW => 'danger',
            $pct < self::COMPLETION_HIGH => 'warning',
            default => 'success',
        };
    }

    /**
     * Which completion chip, if any, this profile sits under. The rows carry it
     * so the shared filter can narrow on it without a server round trip.
     */
    public function completionBand(): ?string
    {
        $pct = (int) $this->profile_completion;

        return match (true) {
            $pct < self::COMPLETION_LOW => 'low',
            $pct >= self::COMPLETION_HIGH => 'high',
            default => null,
        };
    }

    /**
     * Who can see this profile. The three values are the ones the privacy form
     * accepts, and a member who has never set one gets the private default.
     */
    public function visibilityLabel(): string
    {
        return match ($this->profile_visibility) {
            self::VISIBILITY_PUBLIC => 'Everyone, including search engines',
            self::VISIBILITY_MEMBERS => 'Signed-in members only',
            default => 'Nobody but the member',
        };
    }

    public function isThin(): bool
    {
        return (int) $this->profile_completion < self::COMPLETION_LOW;
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('profile_status', 'approved');
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopeOfGender(Builder $query, ?string $gender): Builder
    {
        return $gender ? $query->where('gender', $gender) : $query;
    }
}
