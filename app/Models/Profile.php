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
