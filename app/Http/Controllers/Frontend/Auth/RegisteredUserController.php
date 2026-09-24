<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\Auth\RegisterRequest;
use App\Http\Requests\Frontend\Profile\PhotoUploadRequest;
use App\Http\Requests\Frontend\Profile\ProfileStepRequest;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use App\Services\MatchingService;
use App\Services\ProfileService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisteredUserController extends Controller
{
    /** Number of steps in the profile wizard. */
    private const LAST_STEP = 6;

    public function __construct(
        private ProfileService $profiles,
        private MatchingService $matcher,
    ) {
    }

    /**
     * Step 1 — account credentials.
     */
    public function create(): View
    {
        return view('frontend.auth.register.step-1');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request): User {
            $user = User::create([
                'name' => $request->string('name')->toString(),
                'username' => $this->uniqueUsername($request->string('name')->toString()),
                'email' => $request->string('email')->toString(),
                'phone' => $request->string('phone')->toString(),
                'password' => $request->string('password')->toString(),
                'status' => User::STATUS_ACTIVE,
            ]);

            $this->profiles->profileFor($user);

            return $user;
        });

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('register.step', ['step' => 2])
            ->with('success', 'Account created. Let us build your profile — it takes about two minutes.');
    }

    /**
     * Steps 2-6 — profile details collected progressively.
     */
    public function showStep(Request $request, int $step): View
    {
        abort_unless($step >= 2 && $step <= self::LAST_STEP, 404);

        return view("frontend.auth.register.step-{$step}", [
            'step' => $step,
            'total' => self::LAST_STEP,
            'profileUser' => $request->user()->load([
                'profile', 'education', 'occupation', 'familyDetail', 'lifestyleDetail', 'partnerPreference', 'photos',
            ]),
        ]);
    }

    public function storeStep(ProfileStepRequest $request, int $step): RedirectResponse
    {
        abort_unless($step >= 2 && $step <= self::LAST_STEP, 404);

        $user = $request->user();
        $this->profiles->saveStep($user, $step, $request->validated());

        if ($step === 2 && $request->hasFile('photo')) {
            $photoData = $request->validate(['photo' => PhotoUploadRequest::photoRules()]);

            if ($user->photos()->count() < PhotoUploadRequest::MAX_PHOTOS) {
                $this->profiles->storePhoto($user, $photoData['photo'], makePrimary: true);
            }
        }

        if ($step < self::LAST_STEP) {
            return redirect()
                ->route('register.step', ['step' => $step + 1])
                ->with('success', 'Saved. On to the next step.');
        }

        $this->finish($user);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Your profile is ready. Welcome to Jibon Sathi!');
    }

    public function skipToDashboard(Request $request): RedirectResponse
    {
        return redirect()->route('dashboard')->with('warning', 'You can finish your profile anytime from Settings.');
    }

    private function finish(User $user): void
    {
        $this->profiles->recalculateCompletion($user);

        if (filled($user->partnerPreference?->preferred_gender)) {
            $this->matcher->refreshFor($user, 40);
        }

        if (! $user->notifications()->exists()) {
            $user->notify(new WelcomeNotification);
        }
    }

    private function uniqueUsername(string $name): string
    {
        $base = Str::of($name)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', '')->limit(16, '')->value();
        $base = $base !== '' ? $base : 'member';
        $username = $base;
        $suffix = 1;

        while (User::withTrashed()->where('username', $username)->exists()) {
            $username = $base.$suffix++;
        }

        return $username;
    }
}
