<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\ProfileModerationRequest;
use App\Models\Profile;
use App\Models\ProfilePhoto;
use App\Models\User;
use App\Notifications\ProfileModeratedNotification;
use App\Services\ProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct(private ProfileService $profiles)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in([...array_keys(Profile::STATUSES), 'all'])],
            'q' => ['nullable', 'string', 'max:80'],
            'completion' => ['nullable', Rule::in(['low', 'high'])],
        ]);

        $status = $filters['status'] ?? Profile::STATUS_PENDING;
        $band = $filters['completion'] ?? null;
        $term = trim((string) ($filters['q'] ?? ''));

        $profiles = Profile::query()
            ->with(['user', 'user.primaryPhoto'])
            ->when($status !== 'all', fn ($q) => $q->where('profile_status', $status))
            ->when($band === 'low', fn ($q) => $q->where('profile_completion', '<', Profile::COMPLETION_LOW))
            ->when($band === 'high', fn ($q) => $q->where('profile_completion', '>=', Profile::COMPLETION_HIGH))
            ->when($term !== '', function ($q) use ($term) {
                $like = "%{$term}%";
                $q->where(function ($inner) use ($like) {
                    $inner->whereHas('user', fn ($u) => $u->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone', 'like', $like))
                        ->orWhere('headline', 'like', $like)
                        ->orWhere('city', 'like', $like)
                        ->orWhere('district', 'like', $like);
                });
            })
            // A queue that mixes statuses should still put the work that needs
            // doing at the top, the way the reports queue is ordered.
            ->orderByRaw("FIELD(profile_status, 'pending', 'rejected', 'suspended', 'approved')")
            ->orderBy('profile_completion')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('backend.profiles.index', [
            'profiles' => $profiles,
            'status' => $status,
            'band' => $band,
            'term' => $term,
            'counts' => $this->statusCounts(),
            'bandCounts' => $this->bandCounts($status),
            'bandCountsByStatus' => $this->bandCountsByStatus(),
        ]);
    }

    public function show(Profile $profile): View
    {
        Gate::authorize('moderate', $profile);

        $profile->load(['user.photos', 'user.education', 'user.occupation', 'user.familyDetail', 'user.lifestyleDetail', 'user.partnerPreference', 'user.verifications']);

        return view('backend.profiles.show', ['profile' => $profile, 'user' => $profile->user]);
    }

    public function moderate(ProfileModerationRequest $request, Profile $profile): RedirectResponse
    {
        Gate::authorize('moderate', $profile);

        $decision = $request->validated('decision');
        $note = $request->validated('note');
        $user = $profile->user;

        match ($decision) {
            'approved' => $this->profiles->approve($user, $request->user()->id),
            'rejected' => $this->profiles->reject($user, $note),
            'suspended' => $this->profiles->suspend($user, $note),
            'pending' => $profile->forceFill(['profile_status' => 'pending', 'approved_at' => null])->save(),
        };

        if (in_array($decision, ['approved', 'rejected'], true)) {
            $user->notify(new ProfileModeratedNotification($decision, $note));
        }

        return back()->with('success', 'Profile moderation decision saved.');
    }

    /**
     * Remove an inappropriate photo (and its file) from a member gallery.
     */
    public function destroyPhoto(ProfilePhoto $photo): RedirectResponse
    {
        Gate::authorize('manage', User::class);

        Storage::disk('public')->delete($photo->path);

        $owner = $photo->user;
        $photo->delete();

        if ($owner) {
            $this->profiles->recalculateCompletion($owner);
        }

        return back()->with('success', 'Photo removed from the member gallery.');
    }

    /**
     * Per-status totals for the filter tiles, in one query rather than four.
     *
     * @return array<string, int>
     */
    private function statusCounts(): array
    {
        $counts = array_fill_keys(array_keys(Profile::STATUSES), 0);

        Profile::query()
            ->selectRaw('profile_status, COUNT(*) AS total')
            ->groupBy('profile_status')
            ->get()
            ->each(function ($row) use (&$counts) {
                $counts[$row->profile_status] = (int) $row->total;
            });

        $counts['all'] = array_sum($counts);

        return $counts;
    }

    /**
     * How many profiles sit in each completion band inside the status currently
     * being viewed, so a chip never promises more rows than the active status
     * holds. One query rather than one per band.
     *
     * @return array<string, int>
     */
    private function bandCounts(string $status): array
    {
        $row = Profile::query()
            ->when($status !== 'all', fn ($q) => $q->where('profile_status', $status))
            ->selectRaw(
                'COALESCE(SUM(profile_completion < '.Profile::COMPLETION_LOW.'), 0) AS low,'
                .' COALESCE(SUM(profile_completion >= '.Profile::COMPLETION_HIGH.'), 0) AS high'
            )
            ->first();

        return [
            'low' => (int) ($row->low ?? 0),
            'high' => (int) ($row->high ?? 0),
        ];
    }

    /**
     * The same numbers for every status at once, so the chips can be repainted
     * when the status tile is clicked without another round trip. Keyed by status
     * with an "all" column, since the queue is read one status at a time.
     *
     * @return array<string, array<string, int>>
     */
    private function bandCountsByStatus(): array
    {
        $low = Profile::COMPLETION_LOW;
        $high = Profile::COMPLETION_HIGH;

        return Profile::query()
            ->selectRaw(
                "profile_status,"
                ."CASE WHEN profile_completion < {$low} THEN 'low'"
                ." WHEN profile_completion >= {$high} THEN 'high' END AS band,"
                .'COUNT(*) AS total'
            )
            ->groupBy('profile_status', 'band')
            ->get()
            ->reduce(function (array $matrix, $row) {
                if ($row->band === null) {
                    return $matrix;
                }

                foreach ([$row->profile_status, 'all'] as $key) {
                    $matrix[$key][$row->band] = ($matrix[$key][$row->band] ?? 0) + (int) $row->total;
                }

                return $matrix;
            }, []);
    }
}
