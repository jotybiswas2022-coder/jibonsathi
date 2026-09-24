<?php

namespace App\Http\Controllers\Frontend\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\Settings\NotificationSettingsRequest;
use App\Services\ProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationSettingsController extends Controller
{
    public function __construct(private ProfileService $profiles)
    {
    }

    public function edit(Request $request): View
    {
        return view('frontend.settings.notifications', [
            'user' => $request->user()->load(['profile']),
            'recent' => $request->user()->notifications()->latest()->limit(5)->get(),
        ]);
    }

    public function update(NotificationSettingsRequest $request): RedirectResponse
    {
        $profile = $this->profiles->profileFor($request->user());
        $profile->fill($request->notificationPayload())->save();

        return back()->with('success', 'Notification preferences saved.');
    }
}
