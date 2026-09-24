<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use App\Models\ProfileView;
use App\Models\User;
use App\Services\MatchingService;
use App\Services\ProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProfileController extends Controller
{
    public function __construct(
        private MatchingService $matcher,
        private ProfileService $profiles,
    ) {
    }

    public function show(Request $request, User $user): View
    {
        $viewer = $request->user();

        abort_if($viewer && $viewer->id === $user->id, 404, 'Use your dashboard to preview your own profile.');

        Gate::authorize('view', $user);

        $user->load([
            'profile',
            'photos',
            'primaryPhoto',
            'educations',
            'education',
            'occupations',
            'occupation',
            'familyDetail',
            'lifestyleDetail',
            'partnerPreference',
        ]);

        $viewed = false;
        if ($viewer) {
            $viewed = $this->profiles->recordView($user, $viewer, $request->ip());
        }

        $score = $viewer ? $this->matcher->score($viewer, $user) : null;

        $interest = $viewer ? $user->interestWith($viewer) : null;

        return view('frontend.profiles.show', [
            'profile' => $user,
            'score' => $score,
            'photos' => $user->photos,
            'interest' => $interest,
            'isShortlisted' => $viewer ? $user->isShortlistedBy($viewer) : false,
            'isBlocked' => $viewer ? $viewer->hasBlocked($user) : false,
            'canMessage' => $viewer ? Gate::allows('message', $user) : false,
            'canInteract' => $viewer ? $viewer->canInteractWith($user) : true,
            'viewCount' => ProfileView::query()->where('user_id', $user->id)->count(),
            'mutualMatch' => $viewer ? $this->matcher->isMutual($viewer, $user) : false,
            'viewed' => $viewed,
            'similar' => $viewer ? $this->similar($viewer, $user) : collect(),
        ]);
    }

    /**
     * Nearby members sharing the same division — a soft "similar profiles" rail.
     */
    private function similar(User $viewer, User $profile)
    {
        return User::query()
            ->discoverable()
            ->whereKeyNot([$viewer->id, $profile->id])
            ->whereHas('profile', fn ($q) => $q
                ->where('gender', $profile->profile?->gender)
                ->when($profile->profile?->division, fn ($inner) => $inner->where('division', $profile->profile->division)))
            ->with(['profile', 'primaryPhoto', 'education', 'occupation'])
            ->limit(4)
            ->get();
    }
}
