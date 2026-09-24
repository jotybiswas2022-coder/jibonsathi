<?php

namespace App\Services;

use App\Models\Education;
use App\Models\FamilyDetail;
use App\Models\LifestyleDetail;
use App\Models\Occupation;
use App\Models\PartnerPreference;
use App\Models\Profile;
use App\Models\ProfilePhoto;
use App\Models\ProfileView;
use App\Models\User;
use App\Notifications\ProfileViewedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public const STEPS = [
        1 => 'Account',
        2 => 'Basic Information',
        3 => 'Education & Career',
        4 => 'Family',
        5 => 'Lifestyle',
        6 => 'Partner Preference',
    ];

    /**
     * Create every dependent record for a freshly registered user.
     */
    public function initialize(User $user, array $data): Profile
    {
        return DB::transaction(function () use ($user, $data): Profile {
            $profile = Profile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'gender' => $data['gender'] ?? 'male',
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                    'height_cm' => $data['height_cm'] ?? null,
                    'marital_status' => $data['marital_status'] ?? null,
                    'religion' => $data['religion'] ?? null,
                    'mother_tongue' => $data['mother_tongue'] ?? null,
                    'country' => $data['country'] ?? null,
                    'division' => $data['division'] ?? null,
                    'district' => $data['district'] ?? null,
                    'city' => $data['city'] ?? null,
                    'about_me' => $data['about_me'] ?? null,
                    'profile_status' => 'pending',
                ]
            );

            $this->saveStep($user, 2, $data['basic'] ?? $data);
            $this->saveStep($user, 3, $data['career'] ?? []);
            $this->saveStep($user, 4, $data['family'] ?? []);
            $this->saveStep($user, 5, $data['lifestyle'] ?? []);
            $this->saveStep($user, 6, $data['preference'] ?? []);

            $this->recalculateCompletion($user);

            return $profile;
        });
    }

    /**
     * Persist one step of the six step profile wizard.
     */
    public function saveStep(User $user, int $step, array $data): void
    {
        DB::transaction(function () use ($user, $step, $data) {
            match ($step) {
                1 => $this->saveAccountStep($user, $data),
                2 => $this->saveBasicStep($user, $data),
                3 => $this->saveCareerStep($user, $data),
                4 => $this->saveFamilyStep($user, $data),
                5 => $this->saveLifestyleStep($user, $data),
                6 => $this->savePreferenceStep($user, $data),
                default => null,
            };

            $this->recalculateCompletion($user);
        });
    }

    private function saveAccountStep(User $user, array $data): void
    {
        $user->fill(array_filter([
            'name' => $data['name'] ?? null,
            'phone' => $data['phone'] ?? null,
        ], fn ($value) => $value !== null))->save();
    }

    private function saveBasicStep(User $user, array $data): void
    {
        $attributes = [
            'gender' => $data['gender'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'height_cm' => $data['height_cm'] ?? null,
            'marital_status' => $data['marital_status'] ?? null,
            'religion' => $data['religion'] ?? null,
            'mother_tongue' => $data['mother_tongue'] ?? null,
            'country' => $data['country'] ?? null,
            'division' => $data['division'] ?? null,
            'district' => $data['district'] ?? null,
            'city' => $data['city'] ?? null,
            'about_me' => $data['about_me'] ?? null,
            'headline' => $data['headline'] ?? null,
        ];

        $profile = $this->profileFor($user);

        // Never wipe columns the request did not carry.
        $profile->fill(array_filter($attributes, fn ($value) => $value !== null && $value !== ''));
        $profile->save();
    }

    private function saveCareerStep(User $user, array $data): void
    {
        Education::updateOrCreate(
            ['user_id' => $user->id, 'is_highest' => true],
            [
                'level' => $data['education_level'] ?? $data['level'] ?? null,
                'degree' => $data['degree'] ?? null,
                'institution' => $data['institution'] ?? null,
                'field_of_study' => $data['field_of_study'] ?? null,
                'end_year' => $data['end_year'] ?? null,
            ]
        );

        Occupation::updateOrCreate(
            ['user_id' => $user->id, 'is_current' => true],
            [
                'designation' => $data['profession'] ?? $data['designation'] ?? null,
                'company' => $data['company'] ?? null,
                'employment_type' => $data['employment_type'] ?? null,
                'income_range' => $data['income_range'] ?? null,
                'work_location' => $data['work_location'] ?? null,
            ]
        );
    }

    private function saveFamilyStep(User $user, array $data): void
    {
        FamilyDetail::updateOrCreate(
            ['user_id' => $user->id],
            [
                'family_type' => $data['family_type'] ?? null,
                'family_status' => $data['family_status'] ?? null,
                'father_occupation' => $data['father_occupation'] ?? null,
                'mother_occupation' => $data['mother_occupation'] ?? null,
                'brothers' => $data['brothers'] ?? 0,
                'sisters' => $data['sisters'] ?? 0,
                'family_income_range' => $data['family_income_range'] ?? null,
                'about_family' => $data['about_family'] ?? null,
            ]
        );
    }

    private function saveLifestyleStep(User $user, array $data): void
    {
        LifestyleDetail::updateOrCreate(
            ['user_id' => $user->id],
            [
                'diet' => $data['diet'] ?? null,
                'smoking' => $data['smoking'] ?? null,
                'drinking' => $data['drinking'] ?? null,
                'hobbies' => $this->cleanList($data['hobbies'] ?? []),
                'interests' => $this->cleanList($data['interests'] ?? []),
                'about_lifestyle' => $data['about_lifestyle'] ?? null,
            ]
        );
    }

    private function savePreferenceStep(User $user, array $data): void
    {
        PartnerPreference::updateOrCreate(
            ['user_id' => $user->id],
            [
                'preferred_gender' => $data['preferred_gender'] ?? null,
                'age_min' => $data['age_min'] ?? null,
                'age_max' => $data['age_max'] ?? null,
                'height_min_cm' => $data['height_min_cm'] ?? null,
                'height_max_cm' => $data['height_max_cm'] ?? null,
                'preferred_country' => $data['preferred_country'] ?? null,
                'preferred_division' => $data['preferred_division'] ?? null,
                'preferred_district' => $data['preferred_district'] ?? null,
                'religions' => $this->cleanList($data['religions'] ?? []),
                'marital_statuses' => $this->cleanList($data['marital_statuses'] ?? []),
                'education_level' => $data['education_level'] ?? null,
                'profession' => $data['profession'] ?? null,
                'diet' => $data['diet'] ?? null,
                'smoking' => $data['smoking'] ?? null,
                'drinking' => $data['drinking'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]
        );
    }

    public function profileFor(User $user): Profile
    {
        return $user->profile()->firstOrCreate(
            ['user_id' => $user->id],
            ['gender' => 'male', 'profile_status' => 'pending']
        );
    }

    /**
     * Recalculate the whole-profile completion percentage.
     */
    public function recalculateCompletion(User $user): int
    {
        $user->loadMissing(['profile', 'education', 'occupation', 'familyDetail', 'lifestyleDetail', 'partnerPreference', 'primaryPhoto']);

        $profile = $user->profile;
        $score = 0;

        // Account & contact — 10
        if ($user->name && $user->email && $user->phone) {
            $score += 10;
        }

        // Basic information — 22
        if ($profile) {
            $basic = collect(['gender', 'date_of_birth', 'height_cm', 'marital_status', 'religion', 'country', 'division'])
                ->filter(fn ($field) => filled($profile->{$field}))->count();
            $score += (int) round($basic / 7 * 22);
        }

        // Education & career — 16
        if (filled($user->education?->level)) {
            $score += 8;
        }
        if (filled($user->occupation?->designation) || filled($user->occupation?->company)) {
            $score += 8;
        }

        // Family — 10
        if ($user->familyDetail && filled($user->familyDetail->family_type)) {
            $score += 10;
        }

        // Lifestyle — 14
        if ($user->lifestyleDetail) {
            $lifestyle = collect(['diet', 'smoking', 'drinking'])->filter(fn ($f) => filled($user->lifestyleDetail->{$f}))->count();
            $score += (int) round($lifestyle / 3 * 8);
            if ($user->lifestyleDetail->allInterests() !== []) {
                $score += 6;
            }
        }

        // Partner preference — 16
        if ($user->partnerPreference) {
            $pref = collect(['preferred_gender', 'age_min', 'age_max', 'religion_any', 'education_level', 'diet', 'preferred_country'])
                ->filter(fn ($f) => $f === 'religion_any' ? true : filled($user->partnerPreference->{$f}))->count();
            $score += (int) round($pref / 7 * 16);
        }

        // Photos — 12
        if ($user->primaryPhoto) {
            $score += 12;
        }

        $completion = min(100, $score);

        if ($profile && $profile->profile_completion !== $completion) {
            $profile->forceFill(['profile_completion' => $completion])->save();
            $user->setRelation('profile', $profile->fresh());
        }

        return $completion;
    }

    /**
     * @param  array<int, mixed>  $values
     * @return list<string>
     */
    private function cleanList(array $values): array
    {
        return array_values(array_filter(array_map(
            fn ($value) => is_string($value) ? trim($value) : null,
            $values
        )));
    }

    /* -----------------------------------------------------------------
     |  Photos
     | ----------------------------------------------------------------- */

    public function storePhoto(User $user, UploadedFile $file, bool $makePrimary = false): ProfilePhoto
    {
        $path = $file->store('profiles/'.$user->id, 'public');

        return DB::transaction(function () use ($user, $path, $makePrimary) {
            if ($makePrimary) {
                $user->photos()->update(['is_primary' => false]);
            }

            $photo = $user->photos()->create([
                'path' => $path,
                'is_primary' => $makePrimary || ! $user->photos()->where('is_primary', true)->exists(),
                'status' => 'approved',
                'sort_order' => (int) $user->photos()->max('sort_order') + 1,
            ]);

            if ($photo->is_primary) {
                $user->forceFill(['avatar_path' => $path])->save();
            }

            $this->recalculateCompletion($user);

            return $photo;
        });
    }

    public function makePrimary(User $user, ProfilePhoto $photo): void
    {
        DB::transaction(function () use ($user, $photo) {
            $user->photos()->update(['is_primary' => false]);
            $photo->forceFill(['is_primary' => true])->save();
            $user->forceFill(['avatar_path' => $photo->path])->save();
        });
    }

    public function deletePhoto(User $user, ProfilePhoto $photo): void
    {
        DB::transaction(function () use ($user, $photo) {
            Storage::disk('public')->delete($photo->path);
            $wasPrimary = $photo->is_primary;
            $photo->delete();

            if ($wasPrimary) {
                $next = $user->photos()->first();
                if ($next) {
                    $this->makePrimary($user, $next);
                } else {
                    $user->forceFill(['avatar_path' => null])->save();
                }
            }

            $this->recalculateCompletion($user);
        });
    }

    /* -----------------------------------------------------------------
     |  Views & moderation
     | ----------------------------------------------------------------- */

    /**
     * Record a profile view (throttled to one per viewer per hour).
     */
    public function recordView(User $viewed, User $viewer, ?string $ip = null): bool
    {
        if ($viewed->id === $viewer->id || ! $viewed->profile?->allow_profile_views) {
            return false;
        }

        $recent = ProfileView::query()
            ->where('user_id', $viewed->id)
            ->where('viewer_id', $viewer->id)
            ->where('viewed_at', '>', now()->subHour())
            ->exists();

        if ($recent) {
            return false;
        }

        ProfileView::create([
            'user_id' => $viewed->id,
            'viewer_id' => $viewer->id,
            'ip_address' => $ip,
            'viewed_at' => now(),
        ]);

        $viewed->notify(new ProfileViewedNotification($viewer));

        return true;
    }

    public function approve(User $user, ?int $adminId = null): void
    {
        $profile = $this->profileFor($user);
        $profile->forceFill([
            'profile_status' => 'approved',
            'approved_at' => now(),
        ])->save();
    }

    public function reject(User $user, ?string $note = null): void
    {
        $this->profileFor($user)->forceFill(['profile_status' => 'rejected'])->save();
    }

    public function suspend(User $user, ?string $note = null): void
    {
        DB::transaction(function () use ($user) {
            $user->forceFill(['status' => User::STATUS_SUSPENDED])->save();
            $this->profileFor($user)->forceFill(['profile_status' => 'suspended'])->save();
        });
    }

    /**
     * Bulk-compute completion percentages (used by the seeder).
     */
    public function refreshAll(): void
    {
        User::query()->with(['profile', 'education', 'occupation', 'familyDetail', 'lifestyleDetail', 'partnerPreference', 'primaryPhoto'])
            ->chunkById(50, function ($users) {
                foreach ($users as $user) {
                    $this->recalculateCompletion($user);
                }
            });
    }
}
