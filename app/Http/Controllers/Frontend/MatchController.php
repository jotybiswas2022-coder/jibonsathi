<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\MatchRecord;
use App\Models\User;
use App\Services\DashboardService;
use App\Services\MatchingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public const TABS = [
        'recommended' => 'Recommended',
        'new' => 'New Matches',
        'high' => 'High Compatibility',
    ];

    public function __construct(
        private MatchingService $matcher,
        private DashboardService $dashboard,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $tab = $request->string('tab', 'recommended')->toString();

        if (! array_key_exists($tab, self::TABS)) {
            $tab = 'recommended';
        }

        $query = MatchRecord::query()
            ->where('user_id', $user->id)
            ->with(['matchedUser.profile', 'matchedUser.primaryPhoto', 'matchedUser.education', 'matchedUser.occupation', 'matchedUser.lifestyleDetail', 'matchedUser.partnerPreference']);

        match ($tab) {
            'new' => $query->where('created_at', '>=', now()->subDays(14))->orderByDesc('created_at'),
            'high' => $query->where('match_percentage', '>=', 75)->orderByDesc('match_percentage'),
            default => $query->orderByDesc('match_percentage'),
        };

        $matches = $query->paginate(12)->withQueryString();

        // First visit — build the recommendation set on the fly.
        if ($matches->total() === 0) {
            $this->matcher->refreshFor($user, 60);

            return redirect()->route('matches.index', ['tab' => $tab]);
        }

        $collection = $matches->getCollection()
            ->map(fn (MatchRecord $match) => $match->matchedUser)
            ->filter(fn (?User $candidate) => $candidate !== null && ! $user->blocksWith($candidate))
            ->values();

        return view('frontend.matches.index', [
            'matches' => $matches,
            'tab' => $tab,
            'tabs' => self::TABS,
            'scores' => $this->matcher->decorate($collection, $user),
            'highlights' => $collection->take(3),
            'counts' => [
                'recommended' => MatchRecord::query()->where('user_id', $user->id)->count(),
                'new' => MatchRecord::query()->where('user_id', $user->id)->where('created_at', '>=', now()->subDays(14))->count(),
                'high' => MatchRecord::query()->where('user_id', $user->id)->where('match_percentage', '>=', 75)->count(),
            ],
        ]);
    }

    /**
     * Recalculate recommendations on demand.
     */
    public function refresh(Request $request): \Illuminate\Http\RedirectResponse
    {
        $count = $this->matcher->refreshFor($request->user(), 60);

        return back()->with('success', "Recommendations refreshed — we scored {$count} profiles for you.");
    }
}
