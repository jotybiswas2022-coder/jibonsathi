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
    public function __construct(private ReportService $reports) {}

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
            'q' => ['nullable', 'string', 'max:80'],
        ]);

        $status = $filters['status'] ?? Report::STATUS_PENDING;
        $reason = $filters['reason'] ?? null;
        $term = trim((string) ($filters['q'] ?? ''));

        $reports = Report::query()
            // Only the relations the rows actually read. The total report count on
            // the reported member is the strongest triage signal, so it is counted
            // rather than loaded: a member with four reports should stand out.
            ->with([
                'reporter',
                'handler',
                'reportedUser' => fn ($q) => $q->withCount('reportsReceived'),
                'reportedUser.primaryPhoto',
            ])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($reason, fn ($q) => $q->where('reason', $reason))
            ->when($term !== '', function ($q) use ($term) {
                $like = "%{$term}%";
                $q->where(function ($inner) use ($like) {
                    $inner->where('reason', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhere('admin_note', 'like', $like)
                        ->orWhereHas('reportedUser', fn ($u) => $u->where('name', 'like', $like)->orWhere('email', 'like', $like))
                        ->orWhereHas('reporter', fn ($u) => $u->where('name', 'like', $like));
                });
            })
            // Newest first inside a status, but a queue that mixes statuses should
            // still put the work that needs doing at the top.
            ->orderByRaw("FIELD(status, 'pending', 'investigating', 'resolved', 'dismissed')")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('backend.reports.index', [
            'reports' => $reports,
            'status' => $status,
            'reason' => $reason,
            'term' => $term,
            'reasons' => Report::REASONS,
            'counts' => $this->statusCounts(),
            'reasonCounts' => $this->reasonCounts($status),
            'reasonCountsByStatus' => $this->reasonCountsByStatus(),
        ]);
    }

    public function show(Report $report): View
    {
        Gate::authorize('manage', \App\Models\User::class);

        // The photos are rendered as a grid, and the history rows show the
        // reporter's name, so both are loaded up front rather than per row.
        $report->load([
            'reporter',
            'handler',
            'reportedUser.photos',
            'reportedUser' => fn ($q) => $q->withCount('reportsReceived'),
        ]);

        return view('backend.reports.show', [
            'report' => $report,
            'history' => Report::query()
                ->with('reporter')
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

    /**
     * Per-status totals for the filter tiles, in one query rather than four.
     *
     * @return array<string, int>
     */
    private function statusCounts(): array
    {
        $counts = array_fill_keys([Report::STATUS_PENDING, Report::STATUS_INVESTIGATING, Report::STATUS_RESOLVED, Report::STATUS_DISMISSED], 0);

        Report::query()
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->get()
            ->each(function ($row) use (&$counts) {
                $counts[$row->status] = (int) $row->total;
            });

        $counts['all'] = array_sum($counts);

        return $counts;
    }

    /**
     * How many reports sit under each reason inside the status currently being
     * viewed, so a chip never promises more rows than the active status holds.
     * One query rather than one per reason.
     *
     * @return array<string, int>
     */
    private function reasonCounts(string $status): array
    {
        return Report::query()
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->selectRaw('reason, COUNT(*) AS total')
            ->groupBy('reason')
            ->pluck('total', 'reason')
            ->map(fn ($n) => (int) $n)
            ->all();
    }

    /**
     * The same numbers for every status at once, so the chips can be repainted
     * when the status tile is clicked without another round trip. Keyed by status
     * with an "all" column, since the queue is read one status at a time.
     *
     * @return array<string, array<string, int>>
     */
    private function reasonCountsByStatus(): array
    {
        $matrix = [];

        Report::query()
            ->selectRaw('status, reason, COUNT(*) AS total')
            ->groupBy('status', 'reason')
            ->get()
            ->each(function ($row) use (&$matrix) {
                $matrix[$row->status][$row->reason] = (int) $row->total;
                $matrix['all'][$row->reason] = ($matrix['all'][$row->reason] ?? 0) + (int) $row->total;
            });

        return $matrix;
    }
}
