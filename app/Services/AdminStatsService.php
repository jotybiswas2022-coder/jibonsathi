<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Profile;
use App\Models\ProfileView;
use App\Models\Report;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminStatsService
{
    /**
     * @return array<string, int>
     */
    public function counters(): array
    {
        return [
            'total_users' => User::query()->count(),
            'active_users' => User::query()->where('status', User::STATUS_ACTIVE)->count(),
            'verified_profiles' => Profile::query()->where('verification_status', 'verified')->count(),
            'pending_verification' => Verification::query()->where('status', Verification::STATUS_PENDING)->count(),
            'reported_profiles' => Report::query()->whereIn('status', [Report::STATUS_PENDING, Report::STATUS_INVESTIGATING])->count(),
            'new_registrations' => User::query()->where('created_at', '>=', now()->subDays(7))->count(),
            'active_conversations' => Conversation::query()->where('last_message_at', '>=', now()->subDays(7))->count(),
            'profile_views' => ProfileView::query()->count(),
        ];
    }

    /**
     * Monthly growth series for the last 12 months.
     *
     * @return array{labels: list<string>, users: list<int>, profiles: list<int>}
     */
    public function growthSeries(int $months = 12): array
    {
        $labels = [];
        $users = [];
        $profiles = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->format('M Y');

            $users[] = User::query()
                ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->count();

            $profiles[] = Profile::query()
                ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->count();
        }

        // Ensure the newest month reflects cumulative growth accurately.
        return [
            'labels' => $labels,
            'users' => $users,
            'profiles' => $profiles,
        ];
    }

    /**
     * Daily registrations for the last N days.
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    public function registrationSeries(int $days = 14): array
    {
        $labels = [];
        $values = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d M');
            $values[] = User::query()->whereDate('created_at', $date->toDateString())->count();
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    public function verificationSeries(int $days = 14): array
    {
        $labels = [];
        $values = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d M');
            $values[] = Verification::query()
                ->whereIn('status', [Verification::STATUS_APPROVED, Verification::STATUS_REJECTED])
                ->whereDate('reviewed_at', $date->toDateString())
                ->count();
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    public function reportSeries(int $days = 14): array
    {
        $labels = [];
        $values = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d M');
            $values[] = Report::query()->whereDate('created_at', $date->toDateString())->count();
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    public function genderSplit(): array
    {
        return [
            'labels' => ['Grooms', 'Brides'],
            'values' => [
                Profile::query()->where('gender', 'male')->count(),
                Profile::query()->where('gender', 'female')->count(),
            ],
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    public function reportReasonBreakdown(): array
    {
        $rows = Report::query()
            ->select('reason', DB::raw('count(*) as total'))
            ->groupBy('reason')
            ->pluck('total', 'reason');

        return [
            'labels' => $rows->keys()->map(fn ($reason) => ucwords(str_replace('_', ' ', (string) $reason)))->all(),
            'values' => $rows->values()->map(fn ($total) => (int) $total)->all(),
        ];
    }

    /**
     * Division distribution for the top regions.
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    public function locationBreakdown(int $limit = 6): array
    {
        $rows = Profile::query()
            ->select('division', DB::raw('count(*) as total'))
            ->whereNotNull('division')
            ->groupBy('division')
            ->orderByDesc('total')
            ->limit($limit)
            ->pluck('total', 'division');

        return [
            'labels' => $rows->keys()->all(),
            'values' => $rows->values()->map(fn ($total) => (int) $total)->all(),
        ];
    }

    public function growthPercentage(): float
    {
        $thisMonth = User::query()->where('created_at', '>=', Carbon::now()->startOfMonth())->count();
        $lastMonth = User::query()
            ->whereBetween('created_at', [
                Carbon::now()->subMonth()->startOfMonth(),
                Carbon::now()->subMonth()->endOfMonth(),
            ])->count();

        if ($lastMonth === 0) {
            return $thisMonth > 0 ? 100.0 : 0.0;
        }

        return round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1);
    }
}
