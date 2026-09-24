<?php

namespace App\Services;

use App\Models\MatchRecord;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Rule-based compatibility engine.
 *
 * Each criterion carries a weight; the final percentage blends how well the
 * candidate matches the viewer's preferences (65%) with how well the viewer
 * matches the candidate's preferences (35%) so the score feels mutual.
 */
class MatchingService
{
    /** @var array<string, int> */
    public const WEIGHTS = [
        'gender' => 18,
        'age' => 16,
        'location' => 12,
        'religion' => 12,
        'education' => 10,
        'profession' => 8,
        'height' => 6,
        'diet' => 6,
        'smoking' => 4,
        'drinking' => 4,
        'marital_status' => 4,
    ];

    /** @var array<string, int> */
    private const EDUCATION_RANK = [
        'ssc' => 1, 'hsc' => 2, 'diploma' => 3, 'bachelor' => 4,
        'engineering' => 5, 'medical' => 5, 'masters' => 6, 'phd' => 7, 'other' => 3,
    ];

    /**
     * Evaluate every criterion for `$subject` against `$owner`'s preferences.
     *
     * @return list<array{key: string, label: string, weight: int, matched: bool, detail: string}>
     */
    public function evaluate(User $owner, User $subject): array
    {
        $pref = $owner->partnerPreference;
        $ownerProfile = $owner->profile;
        $candidate = $subject->profile;

        if (! $candidate) {
            return [];
        }

        $candidateAge = $candidate->age();
        $wantedGender = $pref?->preferred_gender
            ?: ($ownerProfile?->gender === 'male' ? 'female' : 'male');

        $criteria = [
            [
                'key' => 'gender',
                'label' => 'Partner gender preference matched',
                'matched' => $candidate->gender === $wantedGender,
                'detail' => 'Looking for '.($wantedGender === 'female' ? 'a bride' : 'a groom'),
            ],
            [
                'key' => 'age',
                'label' => 'Age preference matched',
                'matched' => $candidateAge !== null && $this->within(
                    $candidateAge,
                    $pref?->age_min,
                    $pref?->age_max,
                    defaultLow: 18,
                    defaultHigh: 70
                ),
                'detail' => $pref && ($pref->age_min || $pref->age_max)
                    ? 'You prefer '.$pref->ageRangeLabel()
                    : 'Open to any age',
            ],
            [
                'key' => 'location',
                'label' => 'Location preference matched',
                'matched' => $this->locationMatches($owner, $subject),
                'detail' => $this->locationDetail($owner, $subject),
            ],
            [
                'key' => 'religion',
                'label' => 'Religion preference matched',
                'matched' => $this->listMatches($pref?->religions, $candidate->religion),
                'detail' => filled($pref?->religions) ? 'You prefer '.implode(', ', $pref->religions) : 'Open to all religions',
            ],
            [
                'key' => 'education',
                'label' => 'Similar education level',
                'matched' => $this->educationMatches($owner, $subject),
                'detail' => $this->educationDetail($owner, $subject),
            ],
            [
                'key' => 'profession',
                'label' => 'Profession preference matched',
                'matched' => $this->professionMatches($pref?->profession, $subject),
                'detail' => filled($pref?->profession) ? 'You prefer '.$pref->profession : 'Open to any profession',
            ],
            [
                'key' => 'height',
                'label' => 'Height preference matched',
                'matched' => $this->within($candidate->height_cm, $pref?->height_min_cm, $pref?->height_max_cm, defaultLow: 120, defaultHigh: 220),
                'detail' => $pref && ($pref->height_min_cm || $pref->height_max_cm) ? 'You prefer a specific height range' : 'Open to any height',
            ],
            [
                'key' => 'diet',
                'label' => 'Lifestyle compatibility (diet)',
                'matched' => filled($pref?->diet) ? $pref->diet === $subject->lifestyleDetail?->diet : true,
                'detail' => filled($pref?->diet) ? 'You prefer '.$pref->diet : 'No diet preference',
            ],
            [
                'key' => 'smoking',
                'label' => 'Smoking habit compatibility',
                'matched' => $this->habitMatches($pref?->smoking, $subject->lifestyleDetail?->smoking, ['never', 'trying_to_quit', 'occasionally', 'regularly']),
                'detail' => filled($pref?->smoking) ? 'You prefer '.$pref->smoking : 'No smoking preference',
            ],
            [
                'key' => 'drinking',
                'label' => 'Drinking habit compatibility',
                'matched' => $this->habitMatches($pref?->drinking, $subject->lifestyleDetail?->drinking, ['never', 'occasionally', 'regularly']),
                'detail' => filled($pref?->drinking) ? 'You prefer '.$pref->drinking : 'No drinking preference',
            ],
            [
                'key' => 'marital_status',
                'label' => 'Marital status preference matched',
                'matched' => $this->listMatches($pref?->marital_statuses, $candidate->marital_status),
                'detail' => filled($pref?->marital_statuses) ? 'Based on your marital status preference' : 'Open to any marital status',
            ],
        ];

        return array_map(fn (array $criterion) => $criterion + ['weight' => self::WEIGHTS[$criterion['key']]], $criteria);
    }

    /**
     * Weighted 0-100 compatibility for one direction.
     */
    public function compatibility(User $owner, User $subject): int
    {
        $criteria = $this->evaluate($owner, $subject);

        if ($criteria === []) {
            return 0;
        }

        $total = array_sum(array_column($criteria, 'weight'));
        $earned = 0;

        foreach ($criteria as $criterion) {
            if ($criterion['matched']) {
                $earned += $criterion['weight'];
            }
        }

        return $total > 0 ? (int) round($earned / $total * 100) : 0;
    }

    /**
     * Mutual match payload used across profile cards and detail pages.
     *
     * @return array{percentage: int, reasons: list<array<string, mixed>>, matched: list<array<string, mixed>>, breakdown: list<array<string, mixed>>}
     */
    public function score(User $viewer, User $candidate): array
    {
        $forward = $this->compatibility($viewer, $candidate);
        $backward = $this->compatibility($candidate, $viewer);
        $percentage = (int) round(($forward * 0.65) + ($backward * 0.35));

        $breakdown = $this->evaluate($viewer, $candidate);
        $matched = array_values(array_filter($breakdown, fn ($c) => $c['matched']));

        return [
            'percentage' => max(0, min(100, $percentage)),
            'reasons' => array_values(array_map(
                fn ($c) => ['label' => $c['label'], 'detail' => $c['detail']],
                $matched
            )),
            'matched' => $matched,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Attach scores to a collection of users, keyed by user id.
     *
     * @param  Collection<int, User>  $users
     * @return array<int, array{percentage: int, reasons: list<array<string, mixed>>, matched: list<array<string, mixed>>, breakdown: list<array<string, mixed>>}>
     */
    public function decorate(Collection $users, ?User $viewer): array
    {
        if (! $viewer) {
            return [];
        }

        $scores = [];

        foreach ($users as $user) {
            if ($user->id === $viewer->id) {
                continue;
            }

            $scores[$user->id] = $this->score($viewer, $user);
        }

        return $scores;
    }

    /**
     * Recalculate and persist recommendations for a member.
     */
    public function refreshFor(User $user, int $limit = 80, string $type = MatchRecord::TYPE_RECOMMENDED): int
    {
        $wantedGender = $user->partnerPreference?->preferred_gender
            ?: ($user->profile?->gender === 'male' ? 'female' : 'male');

        $candidates = User::query()
            ->discoverable()
            ->where('id', '!=', $user->id)
            ->whereHas('profile', fn ($q) => $q->where('gender', $wantedGender))
            ->with(['profile', 'partnerPreference', 'lifestyleDetail', 'occupation', 'education'])
            ->limit(200)
            ->get();

        $count = 0;

        DB::transaction(function () use ($user, $candidates, $limit, $type, &$count) {
            $scored = [];

            foreach ($candidates as $candidate) {
                $scored[$candidate->id] = $this->score($user, $candidate);
            }

            arsort($scored);

            foreach (array_slice($scored, 0, $limit, true) as $candidateId => $score) {
                MatchRecord::updateOrCreate(
                    ['user_id' => $user->id, 'matched_user_id' => $candidateId],
                    [
                        'match_percentage' => $score['percentage'],
                        'reasons' => $score['reasons'],
                        'type' => $score['percentage'] >= 75 ? MatchRecord::TYPE_HIGH_COMPATIBILITY : $type,
                        'last_calculated_at' => now(),
                    ]
                );
                $count++;
            }
        });

        return $count;
    }

    public function isMutual(User $viewer, User $candidate): bool
    {
        return MatchRecord::query()
            ->where('user_id', $candidate->id)
            ->where('matched_user_id', $viewer->id)
            ->exists();
    }

    /* -----------------------------------------------------------------
     |  Criterion helpers
     | ----------------------------------------------------------------- */

    private function within(?int $value, ?int $min, ?int $max, int $defaultLow, int $defaultHigh): bool
    {
        if ($value === null) {
            return false;
        }

        $low = $min ?: $defaultLow;
        $high = $max ?: $defaultHigh;

        return $value >= $low && $value <= $high;
    }

    /**
     * @param  list<string>|null  $allowed
     */
    private function listMatches(?array $allowed, ?string $value): bool
    {
        if (blank($allowed)) {
            return true;
        }

        return filled($value) && in_array($value, $allowed, true);
    }

    /**
     * A habit is compatible when the candidate is at least as "clean" as preferred.
     *
     * @param  list<string>  $order  from best to worst
     */
    private function habitMatches(?string $preference, ?string $candidate, array $order): bool
    {
        if (blank($preference)) {
            return true;
        }

        if (blank($candidate)) {
            return false;
        }

        $preferredIndex = array_search($preference, $order, true);
        $candidateIndex = array_search($candidate, $order, true);

        if ($preferredIndex === false || $candidateIndex === false) {
            return $preference === $candidate;
        }

        return $candidateIndex <= $preferredIndex;
    }

    private function locationMatches(User $owner, User $subject): bool
    {
        $pref = $owner->partnerPreference;
        $ownerProfile = $owner->profile;
        $candidate = $subject->profile;

        if (! $candidate) {
            return false;
        }

        if (filled($pref?->preferred_district)) {
            return $candidate->district === $pref->preferred_district
                || $candidate->division === $pref->preferred_division;
        }

        if (filled($pref?->preferred_division)) {
            return $candidate->division === $pref->preferred_division;
        }

        if (filled($pref?->preferred_country)) {
            return $candidate->country === $pref->preferred_country;
        }

        // No explicit preference — reward geographic proximity.
        return $candidate->district === $ownerProfile?->district
            || $candidate->division === $ownerProfile?->division
            || $candidate->country === $ownerProfile?->country;
    }

    private function locationDetail(User $owner, User $subject): string
    {
        $candidate = $subject->profile;
        $ownerProfile = $owner->profile;

        if ($candidate?->district && $candidate->district === $ownerProfile?->district) {
            return "Both of you are in {$candidate->district}";
        }

        if ($candidate?->division && $candidate->division === $ownerProfile?->division) {
            return "Same division ({$candidate->division})";
        }

        return $candidate?->locationLabel() ?? 'Location not shared';
    }

    private function educationMatches(User $owner, User $subject): bool
    {
        $preferred = $owner->partnerPreference?->education_level
            ?? $owner->education?->level;

        if (blank($preferred)) {
            return true;
        }

        $candidateLevel = $subject->education?->level;

        if (blank($candidateLevel)) {
            return false;
        }

        $preferredRank = self::EDUCATION_RANK[$preferred] ?? 3;
        $candidateRank = self::EDUCATION_RANK[$candidateLevel] ?? 3;

        return abs($candidateRank - $preferredRank) <= 1;
    }

    private function educationDetail(User $owner, User $subject): string
    {
        $candidateLevel = $subject->education?->level;

        if (blank($candidateLevel)) {
            return 'Education not shared';
        }

        return 'Candidate holds '.ucwords(str_replace('_', ' ', $candidateLevel)).' level education';
    }

    private function professionMatches(?string $preferred, User $subject): bool
    {
        if (blank($preferred)) {
            return true;
        }

        $designation = (string) $subject->occupation?->designation;
        $company = (string) $subject->occupation?->company;

        if ($designation === '' && $company === '') {
            return false;
        }

        return str_contains(strtolower($designation.' '.$company), strtolower($preferred));
    }
}
