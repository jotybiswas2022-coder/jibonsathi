<?php

namespace App\Http\Controllers\Frontend\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\Settings\PrivacySettingsRequest;
use App\Services\ProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PrivacySettingsController extends Controller
{
    public function __construct(private ProfileService $profiles)
    {
    }

    public function edit(Request $request): View
    {
        return view('frontend.settings.privacy', [
            'user' => $request->user()->load(['profile', 'blocks.blocked.profile', 'blocks.blocked.primaryPhoto']),
        ]);
    }

    public function update(PrivacySettingsRequest $request): RedirectResponse
    {
        $profile = $this->profiles->profileFor($request->user());
        $profile->fill($request->privacyPayload())->save();

        return back()->with('success', 'Privacy preferences saved.');
    }
}
