<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\ProfileModerationRequest;
use App\Models\Profile;
use App\Models\ProfilePhoto;
use App\Models\User;
use App\Notifications\ProfileModeratedNotification;
use App\Services\ProfileService;
use App\Support\Reference;
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
            'status' => ['nullable', Rule::in(array_keys(Reference::profileStatuses()))],
            'q' => ['nullable', 'string', 'max:80'],
            'completion' => ['nullable', Rule::in(['low', 'high'])],
        ]);

        $profiles = Profile::query()
            ->with(['user', 'user.primaryPhoto'])
            ->when(filled($filters['status'] ?? null), fn ($q) => $q->where('profile_status', $filters['status']), fn ($q) => $q->where('profile_status', 'pending'))
            ->when(filled($filters['q'] ?? null), fn ($q) => $q->whereHas('user', fn ($u) => $u->where('name', 'like', '%'.$filters['q'].'%')->orWhere('email', 'like', '%'.$filters['q'].'%')))
            ->when(($filters['completion'] ?? null) === 'low', fn ($q) => $q->where('profile_completion', '<', 60))
            ->when(($filters['completion'] ?? null) === 'high', fn ($q) => $q->where('profile_completion', '>=', 90))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('backend.profiles.index', [
            'profiles' => $profiles,
            'filters' => $filters,
            'counts' => [
                'pending' => Profile::query()->where('profile_status', 'pending')->count(),
                'approved' => Profile::query()->where('profile_status', 'approved')->count(),
                'rejected' => Profile::query()->where('profile_status', 'rejected')->count(),
                'suspended' => Profile::query()->where('profile_status', 'suspended')->count(),
            ],
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
}
