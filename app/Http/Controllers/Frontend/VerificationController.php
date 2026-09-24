<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\Verification\PhoneVerificationRequest;
use App\Http\Requests\Frontend\Verification\ProfileVerificationRequest;
use App\Services\VerificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function __construct(private VerificationService $verifications)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('frontend.verification.index', [
            'statuses' => $this->verifications->statusFor($user),
            'requests' => $user->verifications()->limit(10)->get(),
            'pending' => $user->verifications()->where('status', 'pending')->exists(),
        ]);
    }

    public function submitProfile(ProfileVerificationRequest $request): RedirectResponse
    {
        $this->verifications->submitProfile(
            $request->user(),
            $request->file('document'),
            $request->validated('document_type'),
            $request->validated('note'),
        );

        return redirect()
            ->route('verification.index')
            ->with('success', 'Your document was submitted for review. We usually respond within 24 hours.');
    }

    public function sendPhone(Request $request): RedirectResponse
    {
        $code = $this->verifications->sendPhoneCode($request->user());

        return redirect()
            ->route('verification.index')
            ->with('success', 'A 6 digit code was sent to '.$request->user()->phone.'.')
            ->with('debug_code', app()->isLocal() ? $code : null);
    }

    public function confirmPhone(PhoneVerificationRequest $request): RedirectResponse
    {
        $this->verifications->confirmPhoneCode($request->user(), $request->validated('code'));

        return redirect()
            ->route('verification.index')
            ->with('success', 'Phone number verified. Thank you!');
    }
}
