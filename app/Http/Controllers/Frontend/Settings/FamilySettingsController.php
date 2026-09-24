<?php

namespace App\Http\Controllers\Frontend\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\Profile\FamilyRequest;
use App\Services\ProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FamilySettingsController extends Controller
{
    public function __construct(private ProfileService $profiles)
    {
    }

    public function edit(Request $request): View
    {
        return view('frontend.settings.family', [
            'user' => $request->user()->load(['familyDetail', 'profile']),
        ]);
    }

    public function update(FamilyRequest $request): RedirectResponse
    {
        $this->profiles->saveStep($request->user(), 4, $request->validated());

        return back()->with('success', 'Family details updated.');
    }
}
