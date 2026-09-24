<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_PENDING = 'pending';

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'password',
        'is_admin',
        'status',
        'avatar_path',
        'last_active_at',
        'deactivated_at',
        'email_verified_at',
        'phone_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_active_at' => 'datetime',
            'deactivated_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /* -----------------------------------------------------------------
     |  Relationships
     | ----------------------------------------------------------------- */

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ProfilePhoto::class)->orderByDesc('is_primary')->orderBy('sort_order');
    }

    public function primaryPhoto(): HasOne
    {
        return $this->hasOne(ProfilePhoto::class)->where('is_primary', true);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(Education::class)->orderByDesc('is_highest');
    }

    public function education(): HasOne
    {
        return $this->hasOne(Education::class)->where('is_highest', true);
    }

    public function occupations(): HasMany
    {
        return $this->hasMany(Occupation::class)->orderByDesc('is_current');
    }

    public function occupation(): HasOne
    {
        return $this->hasOne(Occupation::class)->where('is_current', true);
    }

    public function familyDetail(): HasOne
    {
        return $this->hasOne(FamilyDetail::class);
    }

    public function lifestyleDetail(): HasOne
    {
        return $this->hasOne(LifestyleDetail::class);
    }

    public function partnerPreference(): HasOne
    {
        return $this->hasOne(PartnerPreference::class);
    }

    public function sentInterests(): HasMany
    {
        return $this->hasMany(Interest::class, 'sender_id')->latest();
    }

    public function receivedInterests(): HasMany
    {
        return $this->hasMany(Interest::class, 'receiver_id')->latest();
    }

    public function matches(): HasMany
    {
        return $this->hasMany(MatchRecord::class)->orderByDesc('match_percentage');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class)->latest();
    }

    public function favoritedBy(): HasMany
    {
        return $this->hasMany(Favorite::class, 'favorite_user_id')->latest();
    }

    public function conversationsAsOne(): HasMany
    {
        return $this->hasMany(Conversation::class, 'user_one_id');
    }

    public function conversationsAsTwo(): HasMany
    {
        return $this->hasMany(Conversation::class, 'user_two_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function profileViews(): HasMany
    {
        return $this->hasMany(ProfileView::class, 'user_id')->latest('viewed_at');
    }

    public function viewedProfiles(): HasMany
    {
        return $this->hasMany(ProfileView::class, 'viewer_id')->latest('viewed_at');
    }

    public function reportsMade(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function reportsReceived(): HasMany
    {
        return $this->hasMany(Report::class, 'reported_user_id');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class, 'blocker_id');
    }

    public function blockedByUsers(): HasMany
    {
        return $this->hasMany(Block::class, 'blocked_id');
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(Verification::class)->latest();
    }


    /* -----------------------------------------------------------------
     |  Accessors
     | ----------------------------------------------------------------- */

    public function getRouteKeyName(): string
    {
        return 'username';
    }

    protected function initials(): Attribute
    {
        return Attribute::get(function (): string {
            $parts = preg_split('/\s+/', trim((string) $this->name)) ?: [];

            return strtoupper(mb_substr($parts[0] ?? 'J', 0, 1).mb_substr($parts[1] ?? '', 0, 1));
        });
    }

    public function age(): ?int
    {
        $dob = $this->profile?->date_of_birth;

        return $dob ? $dob->age : null;
    }

    public function photoUrl(): string
    {
        $path = $this->primaryPhoto?->path ?? $this->avatar_path;

        return Media::url($path, $this->name);
    }

    public function isOnline(): bool
    {
        if (! $this->profile?->show_online_status) {
            return false;
        }

        return $this->last_active_at !== null && $this->last_active_at->gt(now()->subMinutes(5));
    }

    public function lastSeenLabel(): string
    {
        if (! $this->last_active_at) {
            return 'Offline';
        }

        return $this->last_active_at->diffForHumans();
    }

    public function isVerifiedProfile(): bool
    {
        return $this->profile?->verification_status === 'verified';
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function completion(): int
    {
        return (int) ($this->profile?->profile_completion ?? 0);
    }

    public function genderLabel(): string
    {
        return match ($this->profile?->gender) {
            'male' => 'Groom',
            'female' => 'Bride',
            default => 'Member',
        };
    }

    /* -----------------------------------------------------------------
     |  Interaction guards
     | ----------------------------------------------------------------- */

    public function hasBlocked(User $other): bool
    {
        return Block::query()
            ->where('blocker_id', $this->id)
            ->where('blocked_id', $other->id)
            ->exists();
    }

    public function isBlockedBy(User $other): bool
    {
        return Block::query()
            ->where('blocker_id', $other->id)
            ->where('blocked_id', $this->id)
            ->exists();
    }

    public function blocksWith(User $other): bool
    {
        return $this->hasBlocked($other) || $this->isBlockedBy($other);
    }

    public function canInteractWith(User $other): bool
    {
        return $this->id !== $other->id
            && ! $this->blocksWith($other)
            && $other->status === self::STATUS_ACTIVE;
    }

    public function isShortlistedBy(User $other): bool
    {
        return Favorite::query()
            ->where('user_id', $other->id)
            ->where('favorite_user_id', $this->id)
            ->exists();
    }

    public function interestWith(User $other): ?Interest
    {
        return Interest::query()
            ->where(fn ($q) => $q->where('sender_id', $this->id)->where('receiver_id', $other->id))
            ->orWhere(fn ($q) => $q->where('sender_id', $other->id)->where('receiver_id', $this->id))
            ->latest()
            ->first();
    }

    public function isConnectedWith(User $other): bool
    {
        return Interest::query()
            ->where('status', Interest::STATUS_ACCEPTED)
            ->where(function ($q) use ($other) {
                $q->where(fn ($x) => $x->where('sender_id', $this->id)->where('receiver_id', $other->id))
                    ->orWhere(fn ($x) => $x->where('sender_id', $other->id)->where('receiver_id', $this->id));
            })
            ->exists();
    }

    /* -----------------------------------------------------------------
     |  Scopes
     | ----------------------------------------------------------------- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('is_admin', true);
    }

    public function scopeDiscoverable(Builder $query): Builder
    {
        return $query->active()
            ->whereHas('profile', fn ($q) => $q->where('profile_status', 'approved'));
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('username', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%");
        });
    }
}
