<?php

namespace App\Http\Controllers\Frontend\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\Profile\PartnerPreferenceRequest;
use App\Services\MatchingService;
use App\Services\ProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PreferenceSettingsController extends Controller
{
    public function __construct(
        private ProfileService $profiles,
        private MatchingService $matcher,
    ) {
    }

    public function edit(Request $request): View
    {
        return view('frontend.settings.preference', [
            'user' => $request->user()->load(['partnerPreference', 'profile']),
        ]);
    }

    public function update(PartnerPreferenceRequest $request): RedirectResponse
    {
        $this->profiles->saveStep($request->user(), 6, $request->validated());

        $count = $this->matcher->refreshFor($request->user(), 60);

        return back()->with('success', "Partner preferences saved. We recalculated {$count} recommendations for you.");
    }
}
