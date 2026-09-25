<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\SuccessStory;
use App\Services\DashboardService;
use App\Services\DiscoveryService;
use App\Services\MatchingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(
        private DiscoveryService $discovery,
        private MatchingService $matcher,
        private DashboardService $dashboard,
    ) {
    }

    public function index(Request $request): View
    {
        $viewer = $request->user();

        $featured = $this->discovery->featured(6);
        $newest = $this->discovery->newest(12);
        $recommended = $viewer ? $this->dashboard->recommended($viewer, 6) : collect();

        $gallery = $recommended->isNotEmpty() ? $recommended : $featured;

        return view('frontend.home.index', [
            'featured' => $featured,
            'newest' => $newest,
            'recommended' => $recommended,
            'scores' => $viewer ? $this->matcher->decorate($gallery, $viewer) : [],
            'stories' => SuccessStory::query()
                ->published()
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->latest('married_on')
                ->limit(3)
                ->get(),
            'stats' => [
                'members' => \App\Models\User::query()->active()->count(),
                'verified' => \App\Models\Profile::query()->where('verification_status', 'verified')->count(),
                'stories' => SuccessStory::query()->published()->count(),
                'divisions' => \App\Models\Profile::query()->whereNotNull('division')->distinct('division')->count('division'),
            ],
        ]);
    }
}
