<?php

namespace App\Http\Controllers\Frontend\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\Profile\LifestyleRequest;
use App\Services\ProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LifestyleSettingsController extends Controller
{
    public function __construct(private ProfileService $profiles)
    {
    }

    public function edit(Request $request): View
    {
        return view('frontend.settings.lifestyle', [
            'user' => $request->user()->load(['lifestyleDetail', 'profile']),
        ]);
    }

    public function update(LifestyleRequest $request): RedirectResponse
    {
        $this->profiles->saveStep($request->user(), 5, $request->validated());

        return back()->with('success', 'Lifestyle and interests updated.');
    }
}
