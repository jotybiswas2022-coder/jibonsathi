<?php

namespace App\Http\Controllers\Frontend\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\Profile\BasicInfoRequest;
use App\Services\ProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileSettingsController extends Controller
{
    public function __construct(private ProfileService $profiles)
    {
    }

    public function edit(Request $request): View
    {
        return view('frontend.settings.profile', [
            'user' => $request->user()->load(['profile', 'photos', 'primaryPhoto']),
        ]);
    }

    public function update(BasicInfoRequest $request): RedirectResponse
    {
        $this->profiles->saveStep($request->user(), 2, $request->validated());

        return back()->with('success', 'Basic information updated.');
    }
}
