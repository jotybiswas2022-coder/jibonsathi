<?php

namespace App\Http\Controllers\Frontend\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\Profile\CareerRequest;
use App\Services\ProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CareerSettingsController extends Controller
{
    public function __construct(private ProfileService $profiles)
    {
    }

    public function edit(Request $request): View
    {
        return view('frontend.settings.career', [
            'user' => $request->user()->load(['education', 'occupation', 'profile']),
        ]);
    }

    public function update(CareerRequest $request): RedirectResponse
    {
        $this->profiles->saveStep($request->user(), 3, $request->validated());

        return back()->with('success', 'Education and career details updated.');
    }
}
