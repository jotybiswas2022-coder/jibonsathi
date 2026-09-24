<?php

namespace App\Http\Controllers\Frontend\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\Settings\AccountActionRequest;
use App\Models\ProfileView;
use App\Services\ProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AccountSettingsController extends Controller
{
    public function __construct(private ProfileService $profiles)
    {
    }

    public function edit(Request $request): View
    {
        return view('frontend.settings.account', [
            'user' => $request->user()->load(['profile']),
        ]);
    }

    public function deactivate(AccountActionRequest $request): RedirectResponse
    {
        $user = $request->user();

        DB::transaction(function () use ($user) {
            $user->forceFill([
                'status' => 'inactive',
                'deactivated_at' => now(),
            ])->save();

            $this->profiles->profileFor($user)->forceFill(['profile_visibility' => 'private'])->save();
        });

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Your account is deactivated. Sign in any time to reactivate it.');
    }

    public function reactivate(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->forceFill(['status' => 'active', 'deactivated_at' => null])->save();

        return back()->with('success', 'Welcome back — your account is active again.');
    }

    public function destroy(AccountActionRequest $request): RedirectResponse
    {
        $user = $request->user();

        DB::transaction(function () use ($user) {
            // Remove personal media before the soft delete.
            foreach ($user->photos as $photo) {
                Storage::disk('public')->delete($photo->path);
            }

            ProfileView::query()
                ->where('user_id', $user->id)
                ->orWhere('viewer_id', $user->id)
                ->delete();

            $user->forceFill(['status' => 'inactive', 'deactivated_at' => now()])->save();
            $user->delete();
        });

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Your account and personal data have been removed. We are sorry to see you go.');
    }
}
