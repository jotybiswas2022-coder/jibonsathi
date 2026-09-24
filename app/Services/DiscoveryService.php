<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

class DiscoveryService
{
    public const SORTS = [
        'recommended' => 'Recommended',
        'newest' => 'Newest Members',
        'recently_active' => 'Recently Active',
        'completion' => 'Profile Completion',
        'age_asc' => 'Age: Low to High',
        'age_desc' => 'Age: High to Low',
    ];

    public function __construct(private MatchingService $matcher)
    {
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function search(?User $viewer, array $filters, int $perPage = 12): LengthAwarePaginator
    {
        $sort = $filters['sort'] ?? 'recommended';

        $query = $this->buildQuery($viewer, $filters);

        // "Recommended" needs a computed score, so rank in PHP then paginate.
        if ($sort === 'recommended' && $viewer) {
            return $this->rankedPaginate($viewer, $query, $perPage);
        }

        $this->applySort($query, $sort);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Profiles to show on the home page before login (no viewer).
     */
    public function featured(int $limit = 6): Collection
    {
        return $this->baseQuery()
            ->whereHas('profile', fn ($q) => $q->whereNotNull('date_of_birth'))
            ->orderByDesc('profiles.profile_completion')
            ->limit($limit)
            ->get();
    }

    /**
     * Discoverable members for a signed-in viewer.
     *
     * @param  array<string, mixed>  $filters
     */
    public function buildQuery(?User $viewer, array $filters = []): Builder
    {
        $query = $this->baseQuery();

        if ($viewer) {
            $query->where('users.id', '!=', $viewer->id);

            $blocked = $this->blockedIds($viewer);
            if ($blocked !== []) {
                $query->whereNotIn('users.id', $blocked);
            }
        }

        $this->applyFilters($query, $viewer, $filters);

        return $query;
    }

    private function baseQuery(): Builder
    {
        return User::query()
            ->select('users.*')
            ->join('profiles', 'profiles.user_id', '=', 'users.id')
            ->whereNull('users.deleted_at')
            ->where('users.status', User::STATUS_ACTIVE)
            ->where('profiles.profile_status', 'approved')
            ->with([
                'profile',
                'primaryPhoto',
                'education',
                'occupation',
                'familyDetail',
                'lifestyleDetail',
                'partnerPreference',
            ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, ?User $viewer, array $filters): void
    {
        $query
            ->when(filled($filters['gender'] ?? null), fn (Builder $q) => $q->where('profiles.gender', $filters['gender']))
            ->when(filled($filters['marital_status'] ?? null), fn (Builder $q) => $q->where('profiles.marital_status', $filters['marital_status']))
            ->when(filled($filters['religion'] ?? null), fn (Builder $q) => $q->where('profiles.religion', $filters['religion']))
            ->when(filled($filters['country'] ?? null), fn (Builder $q) => $q->where('profiles.country', $filters['country']))
            ->when(filled($filters['division'] ?? null), fn (Builder $q) => $q->where('profiles.division', $filters['division']))
            ->when(filled($filters['district'] ?? null), fn (Builder $q) => $q->where('profiles.district', $filters['district']))
            ->when(filled($filters['city'] ?? null), fn (Builder $q) => $q->where('profiles.city', 'like', '%'.$filters['city'].'%'))
            ->when(filled($filters['location'] ?? null), function (Builder $q) use ($filters) {
                $term = $filters['location'];
                $q->where(function (Builder $inner) use ($term) {
                    $inner->where('profiles.city', 'like', "%{$term}%")
                        ->orWhere('profiles.district', 'like', "%{$term}%")
                        ->orWhere('profiles.division', 'like', "%{$term}%")
                        ->orWhere('profiles.country', 'like', "%{$term}%");
                });
            })
            ->when(filled($filters['education'] ?? null), fn (Builder $q) => $q->whereHas('education', fn ($e) => $e->where('level', $filters['education'])))
            ->when(filled($filters['profession'] ?? null), fn (Builder $q) => $q->whereHas('occupation', fn ($o) => $o->where('designation', 'like', '%'.$filters['profession'].'%')))
            ->when(filled($filters['income'] ?? null), fn (Builder $q) => $q->whereHas('occupation', fn ($o) => $o->where('income_range', $filters['income'])))
            ->when(filled($filters['diet'] ?? null), fn (Builder $q) => $q->whereHas('lifestyleDetail', fn ($l) => $l->where('diet', $filters['diet'])))
            ->when(filled($filters['smoking'] ?? null), fn (Builder $q) => $q->whereHas('lifestyleDetail', fn ($l) => $l->where('smoking', $filters['smoking'])))
            ->when(filled($filters['drinking'] ?? null), fn (Builder $q) => $q->whereHas('lifestyleDetail', fn ($l) => $l->where('drinking', $filters['drinking'])))
            ->when(filled($filters['family_type'] ?? null), fn (Builder $q) => $q->whereHas('familyDetail', fn ($f) => $f->where('family_type', $filters['family_type'])))
            ->when(filled($filters['verified'] ?? null), fn (Builder $q) => $q->where('profiles.verification_status', 'verified'))
            ->when(filled($filters['q'] ?? null), fn (Builder $q) => $q->where(function (Builder $inner) use ($filters) {
                $term = $filters['q'];
                $inner->where('users.name', 'like', "%{$term}%")
                    ->orWhere('users.username', 'like', "%{$term}%")
                    ->orWhere('profiles.city', 'like', "%{$term}%");
            }));

        // Age range via date of birth boundaries.
        if (filled($filters['age_from'] ?? null)) {
            $query->where('profiles.date_of_birth', '<=', now()->subYears((int) $filters['age_from'])->toDateString());
        }

        if (filled($filters['age_to'] ?? null)) {
            $query->where('profiles.date_of_birth', '>=', now()->subYears((int) $filters['age_to'] + 1)->addDay()->toDateString());
        }

        // Height range.
        if (filled($filters['height_from'] ?? null)) {
            $query->where('profiles.height_cm', '>=', (int) $filters['height_from']);
        }

        if (filled($filters['height_to'] ?? null)) {
            $query->where('profiles.height_cm', '<=', (int) $filters['height_to']);
        }

        // Smart default: respect the viewer's partner preference unless overridden.
        if (filled($filters['apply_preference'] ?? null) && $viewer?->partnerPreference) {
            $pref = $viewer->partnerPreference;

            $query->when($pref->preferred_gender, fn (Builder $q) => $q->where('profiles.gender', $pref->preferred_gender))
                ->when($pref->religions, fn (Builder $q) => $q->whereIn('profiles.religion', $pref->religions))
                ->when($pref->marital_statuses, fn (Builder $q) => $q->whereIn('profiles.marital_status', $pref->marital_statuses));
        }
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'newest' => $query->orderByDesc('users.created_at'),
            'recently_active' => $query->orderByDesc('users.last_active_at'),
            'completion' => $query->orderByDesc('profiles.profile_completion'),
            'age_asc' => $query->orderByDesc('profiles.date_of_birth'),
            'age_desc' => $query->orderBy('profiles.date_of_birth'),
            default => $query->orderByDesc('profiles.profile_completion')->orderByDesc('users.created_at'),
        };
    }

    private function rankedPaginate(User $viewer, Builder $query, int $perPage): Paginator
    {
        $candidates = $query->limit(200)->get();

        $scored = $candidates->map(function (User $candidate) use ($viewer) {
            return ['user' => $candidate, 'score' => $this->matcher->score($viewer, $candidate)];
        })->sortByDesc(fn ($row) => $row['score']['percentage'])->values();

        $page = max(1, (int) request()->input('page', 1));

        return new Paginator(
            $scored->slice(($page - 1) * $perPage, $perPage)->pluck('user')->values(),
            $scored->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * @return list<int>
     */
    private function blockedIds(User $viewer): array
    {
        return $viewer->blocks()->pluck('blocked_id')
            ->merge($viewer->blockedByUsers()->pluck('blocker_id'))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Score map for a page of results.
     *
     * @param  iterable<User>  $users
     * @return array<int, mixed>
     */
    public function scoresFor(iterable $users, ?User $viewer): array
    {
        return $this->matcher->decorate(collect($users), $viewer);
    }

    /**
     * Aggregated numbers for admin insight panels.
     *
     * @return array<string, int>
     */
    public function genderBreakdown(): array
    {
        return [
            'male' => Profile::query()->where('gender', 'male')->count(),
            'female' => Profile::query()->where('gender', 'female')->count(),
        ];
    }
}
