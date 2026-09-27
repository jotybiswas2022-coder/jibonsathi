<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\MemberDetailsRequest;
use App\Http\Requests\Backend\UserUpdateRequest;
use App\Models\User;
use App\Notifications\AccountStatusNotification;
use App\Services\ProfileService;
use App\Support\Reference;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
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

        return view('backend.users.index', [
            'users' => $users,
            'filters' => $filters,
            'status' => $filters['status'] ?? null,
            'counts' => $this->statusCounts(),
        ]);
    }

    /**
     * Per-status totals for the filter tiles, in one query rather than five.
     *
     * @return array<string, int>
     */
    private function statusCounts(): array
    {
        $counts = array_fill_keys(array_keys(Reference::userStatuses()), 0);

        User::query()
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->get()
            ->each(function ($row) use (&$counts) {
                $counts[$row->status] = (int) $row->total;
            });

        $counts['all'] = array_sum($counts);

        return $counts;
    }

    public function create(): View
    {
        Gate::authorize('manage', User::class);

        return view('backend.users.create', [
            'statuses' => Reference::userStatuses(),
        ]);
    }

    /**
     * Create a member and, in the same submit, everything we know about them.
     */
    public function store(MemberDetailsRequest $request): RedirectResponse
    {
        Gate::authorize('manage', User::class);

        $user = DB::transaction(function () use ($request): User {
            $user = User::create([
                'name' => $request->validated('name'),
                'username' => $this->uniqueUsername($request->validated('username') ?: $request->validated('name')),
                'email' => $request->validated('email'),
                'phone' => $request->validated('phone'),
                'password' => $request->validated('password'),
                'status' => $request->validated('status'),
                'is_admin' => $request->boolean('is_admin'),
            ]);

            $this->profiles->saveFromAdmin($user, $request->profilePayload());

            if ($request->hasFile('photo')) {
                $this->profiles->storePhoto($user, $request->file('photo'), makePrimary: true);
            }

            return $user;
        });

        return redirect()
            ->route('backend.users.show', $user)
            ->with('success', "{$user->name} has been created.");
    }

    /**
     * Every detail about a member, on one form. Reachable from the profile page,
     * which is where a moderator is when a member phones to correct something.
     */
    public function details(User $user): View
    {
        Gate::authorize('manage', User::class);

        $user->load(['profile', 'education', 'occupation', 'familyDetail', 'lifestyleDetail', 'partnerPreference']);

        return view('backend.users.details', [
            'user' => $user,
            'statuses' => Reference::userStatuses(),
        ]);
    }

    public function updateDetails(MemberDetailsRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('manage', User::class);

        DB::transaction(function () use ($request, $user): void {
            $account = [
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'phone' => $request->validated('phone'),
                'status' => $request->validated('status'),
                'is_admin' => $request->boolean('is_admin'),
            ];

            if ($request->filled('username') && $request->validated('username') !== $user->username) {
                $account['username'] = $this->uniqueUsername($request->validated('username'), $user);
            }

            $user->fill($account)->save();

            if ($request->filled('password')) {
                $user->forceFill(['password' => $request->validated('password')])->save();
            }

            $this->profiles->saveFromAdmin($user, $request->profilePayload());

            if ($request->hasFile('photo')) {
                $this->profiles->storePhoto($user, $request->file('photo'));
            }
        });

        return redirect()
            ->route('backend.users.show', $user)
            ->with('success', "{$user->name}'s details have been updated.");
    }

    /**
     * A username the admin typed wins, so it is only tidied up (lower-cased, no
     * punctuation) and never rebuilt from the name. When the tidied form is
     * already taken by somebody else, a number is added until it is free.
     */
    private function uniqueUsername(string $source, ?User $ignore = null): string
    {
        $base = Str::of($source)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', '')->limit(16, '')->value();

        if ($base === '') {
            $base = 'member';
        }

        $candidate = $base;
        $suffix = 1;

        while (
            User::withTrashed()
                ->where('username', $candidate)
                ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->getKey()))
                ->exists()
        ) {
            $candidate = $base.$suffix++;
        }

        return $candidate;
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
