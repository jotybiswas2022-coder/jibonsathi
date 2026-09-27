<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\Verification\ProfileVerificationRequest;
use App\Models\Verification;
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
            'identity' => $this->verifications->identityStatus($user),
            // Document checks only. This page never offered a phone code, and a
            // phone row here would be history the member cannot act on.
            // Newest first: without the order the database was free to hand back
            // the ten oldest rows.
            'requests' => $user->verifications()
                ->where('type', Verification::TYPE_PROFILE)
                ->latest()
                ->limit(10)
                ->get(),
            'pending' => $user->verifications()
                ->where('type', Verification::TYPE_PROFILE)
                ->where('status', Verification::STATUS_PENDING)
                ->exists(),
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
}
