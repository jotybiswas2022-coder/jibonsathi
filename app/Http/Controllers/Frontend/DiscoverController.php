<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\Discover\SearchRequest;
use App\Services\DiscoveryService;
use App\Services\MatchingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DiscoverController extends Controller
{
    public function __construct(
        private DiscoveryService $discovery,
        private MatchingService $matcher,
    ) {
    }

    public function index(SearchRequest $request): View
    {
        $user = $request->user();
        $filters = $request->filters() ?: ['sort' => 'recommended'];

        if (! isset($filters['gender']) && $user?->partnerPreference?->preferred_gender) {
            $filters['gender'] = $user->partnerPreference->preferred_gender;
        }

        $results = $this->discovery->search($user, $filters, 12);

        $view = view('frontend.discover.index', [
            'results' => $results,
            'filters' => $filters,
            'sorts' => DiscoveryService::SORTS,
            'scores' => $this->discovery->scoresFor($results->getCollection(), $user),
        ]);

        // Live filtering: only re-render the results grid for AJAX requests.
        if ($request->boolean('partial') || $request->ajax()) {
            $view = view('frontend.discover.partials.results', [
                'results' => $results,
                'filters' => $filters,
                'scores' => $this->discovery->scoresFor($results->getCollection(), $user),
            ]);
        }

        return $view;
    }
}
