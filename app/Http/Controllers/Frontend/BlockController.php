<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BlockController extends Controller
{
    public function __construct(private ReportService $reports)
    {
    }

    public function index(Request $request): View
    {
        $blocked = $request->user()
            ->blocks()
            ->with(['blocked.profile', 'blocked.primaryPhoto'])
            ->latest()
            ->paginate(12);

        return view('frontend.settings.blocked', ['blocked' => $blocked]);
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('block', $user);

        $request->validate(['reason' => ['nullable', 'string', 'max:160']]);

        $this->reports->block($request->user(), $user, $request->input('reason'));

        return back()->with('success', $user->name.' has been blocked. They can no longer interact with you.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->reports->unblock($request->user(), $user);

        return back()->with('success', $user->name.' has been unblocked.');
    }
}
