<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\Auth\LoginRequest;
use App\Models\User;
use App\Services\MatchingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('frontend.auth.login');
    }

    public function store(LoginRequest $request, MatchingService $matcher): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->hitRateLimiter();

            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $user = $request->user();

        if ($user->status === User::STATUS_SUSPENDED) {
            Auth::logout();
            RateLimiter::clear($request->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'This account has been suspended. Please contact support.',
            ]);
        }

        RateLimiter::clear($request->throttleKey());
        $request->session()->regenerate();

        $user->forceFill(['last_active_at' => now()])->saveQuietly();

        // Keep recommendations fresh without blocking the login flow.
        $matcher->refreshFor($user, 30);

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been signed out.');
    }
}
