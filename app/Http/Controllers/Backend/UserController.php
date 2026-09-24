<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\UserUpdateRequest;
use App\Models\User;
use App\Notifications\AccountStatusNotification;
use App\Services\ProfileService;
use App\Support\Reference;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(private ProfileService $profiles)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', Rule::in(array_keys(Reference::userStatuses()))],
            'gender' => ['nullable', Rule::in(array_keys(Reference::genders()))],
            'verified' => ['nullable', 'boolean'],
            'role' => ['nullable', Rule::in(['admin', 'member'])],
            'sort' => ['nullable', Rule::in(['newest', 'oldest', 'name'])],
        ]);

        $users = User::query()
            ->with(['profile', 'primaryPhoto'])
            ->search($filters['q'] ?? null)
            ->when(filled($filters['status'] ?? null), fn ($q) => $q->where('status', $filters['status']))
            ->when(filled($filters['verified'] ?? null), fn ($q) => $q->whereHas('profile', fn ($p) => $p->where('verification_status', 'verified')))
            ->when(($filters['role'] ?? null) === 'admin', fn ($q) => $q->where('is_admin', true))
            ->when(($filters['role'] ?? null) === 'member', fn ($q) => $q->where('is_admin', false))
            ->when(filled($filters['gender'] ?? null), fn ($q) => $q->whereHas('profile', fn ($p) => $p->where('gender', $filters['gender'])))
            ->when(($filters['sort'] ?? 'newest') === 'oldest', fn ($q) => $q->oldest())
            ->when(($filters['sort'] ?? null) === 'name', fn ($q) => $q->orderBy('name'))
            ->when(! isset($filters['sort']) || $filters['sort'] === 'newest', fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        return view('backend.users.index', compact('users', 'filters'));
    }

    public function show(User $user): View
    {
        Gate::authorize('manage', User::class);

        $user->load([
            'profile', 'photos', 'education', 'occupation', 'familyDetail',
            'lifestyleDetail', 'partnerPreference', 'verifications',
            'reportsReceived', 'reportsMade',
        ]);

        return view('backend.users.show', [
            'user' => $user,
            'activity' => [
                'interests_sent' => $user->sentInterests()->count(),
                'interests_received' => $user->receivedInterests()->count(),
                'profile_views' => $user->profileViews()->count(),
                'reports' => $user->reportsReceived()->count(),
            ],
        ]);
    }

    public function edit(User $user): View
    {
        Gate::authorize('manage', User::class);

        return view('backend.users.edit', [
            'user' => $user->load(['profile']),
            'statuses' => Reference::userStatuses(),
        ]);
    }

    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        $previousStatus = $user->status;

        $user->fill([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'status' => $request->validated('status'),
        ])->save();

        if ($previousStatus !== $user->status && in_array($user->status, ['suspended', 'active'], true)) {
            $user->notify(new AccountStatusNotification($user->status, $request->validated('admin_note')));
        }

        return redirect()
            ->route('backend.users.show', $user)
            ->with('success', 'Member account updated.');
    }

    public function verify(User $user): RedirectResponse
    {
        Gate::authorize('verify', $user);

        $this->profiles->approve($user);

        return back()->with('success', "{$user->name}'s profile has been approved and verified.");
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('suspend', $user);

        $note = $request->validate(['note' => ['nullable', 'string', 'max:500']])['note'] ?? null;

        $this->profiles->suspend($user, $note);
        $user->notify(new AccountStatusNotification('suspended', $note));

        return back()->with('success', "{$user->name} has been suspended.");
    }

    public function activate(User $user): RedirectResponse
    {
        Gate::authorize('activate', $user);

        $user->forceFill(['status' => User::STATUS_ACTIVE])->save();
        $this->profiles->profileFor($user)->forceFill(['profile_status' => 'approved'])->save();
        $user->notify(new AccountStatusNotification('active'));

        return back()->with('success', "{$user->name} has been reactivated.");
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $user->delete();

        return redirect()
            ->route('backend.users.index')
            ->with('success', "{$user->name} has been removed.");
    }
}
