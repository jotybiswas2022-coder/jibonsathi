<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\Interaction\ReportRequest;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function __construct(private ReportService $reports)
    {
    }

    public function store(ReportRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('report', $user);

        $this->reports->create(
            $request->user(),
            $user,
            $request->validated('reason'),
            $request->validated('description'),
        );

        if ($request->boolean('block_user')) {
            $this->reports->block($request->user(), $user, 'Blocked while reporting');
        }

        return redirect()
            ->route('profiles.show', $user->username)
            ->with('success', 'Thank you — our moderation team will review this report shortly.');
    }

    public function index(Request $request): View
    {
        $reports = $request->user()
            ->reportsMade()
            ->with(['reportedUser.profile', 'reportedUser.primaryPhoto'])
            ->latest()
            ->paginate(10);

        return view('frontend.reports.index', compact('reports'));
    }
}
