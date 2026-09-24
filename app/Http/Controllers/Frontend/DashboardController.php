<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Services\MatchingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboard,
        private MatchingService $matcher,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $data = $this->dashboard->forUser($user);

        return view('frontend.dashboard.index', [
            ...$data,
            'scores' => $this->matcher->decorate($data['recommended'], $user),
        ]);
    }
}
