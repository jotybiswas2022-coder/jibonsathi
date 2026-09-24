<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\ReportDecisionRequest;
use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function __construct(private ReportService $reports)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in([
                Report::STATUS_PENDING,
                Report::STATUS_INVESTIGATING,
                Report::STATUS_RESOLVED,
                Report::STATUS_DISMISSED,
                'all',
            ])],
            'reason' => ['nullable', Rule::in(array_keys(Report::REASONS))],
        ]);

        $status = $filters['status'] ?? Report::STATUS_PENDING;

        $reports = Report::query()
            ->with(['reporter.profile', 'reportedUser.profile', 'reportedUser.primaryPhoto'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when(filled($filters['reason'] ?? null), fn ($q) => $q->where('reason', $filters['reason']))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('backend.reports.index', [
            'reports' => $reports,
            'status' => $status,
            'reason' => $filters['reason'] ?? null,
            'reasons' => Report::REASONS,
            'counts' => [
                'pending' => Report::query()->where('status', Report::STATUS_PENDING)->count(),
                'investigating' => Report::query()->where('status', Report::STATUS_INVESTIGATING)->count(),
                'resolved' => Report::query()->where('status', Report::STATUS_RESOLVED)->count(),
                'dismissed' => Report::query()->where('status', Report::STATUS_DISMISSED)->count(),
            ],
        ]);
    }

    public function show(Report $report): View
    {
        Gate::authorize('manage', \App\Models\User::class);

        $report->load(['reporter.profile', 'reportedUser.profile', 'reportedUser.photos', 'handler']);

        return view('backend.reports.show', [
            'report' => $report,
            'history' => Report::query()
                ->where('reported_user_id', $report->reported_user_id)
                ->whereKeyNot($report->id)
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }

    public function decide(ReportDecisionRequest $request, Report $report): RedirectResponse
    {
        Gate::authorize('manage', \App\Models\User::class);

        $decision = $request->validated('decision');
        $note = $request->validated('note');
        $admin = $request->user();

        match ($decision) {
            Report::STATUS_INVESTIGATING => $this->reports->markInvestigating($report, $admin, $note),
            Report::STATUS_RESOLVED => $this->reports->resolve($report, $admin, $note),
            Report::STATUS_DISMISSED => $this->reports->dismiss($report, $admin, $note),
            'suspend' => $this->reports->suspendReportedUser($report, $admin, $note),
        };

        return back()->with('success', 'Report updated.');
    }
}
