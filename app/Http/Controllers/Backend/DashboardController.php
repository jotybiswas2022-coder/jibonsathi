<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Models\Verification;
use App\Services\AdminStatsService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __construct(private AdminStatsService $stats)
    {
    }

    public function index(): View
    {
        return view('backend.dashboard.index', [
            'counters' => $this->stats->counters(),
            'growth' => $this->stats->growthSeries(),
            'registrations' => $this->stats->registrationSeries(),
            'verifications' => $this->stats->verificationSeries(),
            'reports' => $this->stats->reportSeries(),
            'genders' => $this->stats->genderSplit(),
            'reasons' => $this->stats->reportReasonBreakdown(),
            'locations' => $this->stats->locationBreakdown(),
            'growthRate' => $this->stats->growthPercentage(),
            'latestUsers' => User::query()->latest()->limit(6)->with('profile')->get(),
            'pendingVerifications' => Verification::query()
                ->where('status', Verification::STATUS_PENDING)
                ->with(['user.profile'])
                ->latest()
                ->limit(5)
                ->get(),
            'openReports' => Report::query()
                ->whereIn('status', [Report::STATUS_PENDING, Report::STATUS_INVESTIGATING])
                ->with(['reporter', 'reportedUser'])
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
