<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\User;
use App\Notifications\ProfileShortlistedNotification;
use App\Services\MatchingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{
    public function __construct(private MatchingService $matcher)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $favorites = Favorite::query()
            ->where('user_id', $user->id)
            ->with(['favoriteUser.profile', 'favoriteUser.primaryPhoto', 'favoriteUser.education', 'favoriteUser.occupation', 'favoriteUser.lifestyleDetail', 'favoriteUser.partnerPreference'])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $profiles = $favorites->getCollection()
            ->map(fn (Favorite $favorite) => $favorite->favoriteUser)
            ->filter()
            ->values();

        return view('frontend.favorites.index', [
            'favorites' => $favorites,
            'scores' => $this->matcher->decorate($profiles, $user),
            'total' => Favorite::query()->where('user_id', $user->id)->count(),
        ]);
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('shortlist', $user);

        $added = DB::transaction(function () use ($request, $user) {
            return Favorite::firstOrCreate([
                'user_id' => $request->user()->id,
                'favorite_user_id' => $user->id,
            ])->wasRecentlyCreated;
        });

        if ($added && $request->user()->profile?->notify_shortlists !== false) {
            $user->notify(new ProfileShortlistedNotification($request->user()));
        }

        return back()->with('success', $added
            ? "{$user->name} added to your shortlist."
            : "{$user->name} is already on your shortlist.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        Favorite::query()
            ->where('user_id', $request->user()->id)
            ->where('favorite_user_id', $user->id)
            ->delete();

        return back()->with('success', "{$user->name} removed from your shortlist.");
    }
}
