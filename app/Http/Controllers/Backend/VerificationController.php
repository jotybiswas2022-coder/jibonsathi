<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\VerificationDecisionRequest;
use App\Models\Verification;
use App\Services\VerificationService;
use App\Support\Reference;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VerificationController extends Controller
{
    public function __construct(private VerificationService $verifications)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'string'],
            'type' => ['nullable', 'string'],
        ]);

        $status = $filters['status'] ?? 'pending';

        $verifications = Verification::query()
            ->with(['user.profile', 'user.primaryPhoto', 'reviewer'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when(filled($filters['type'] ?? null), fn ($q) => $q->where('type', $filters['type']))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('backend.verification.index', [
            'verifications' => $verifications,
            'status' => $status,
            'type' => $filters['type'] ?? null,
            'counts' => [
                'pending' => Verification::query()->where('status', 'pending')->count(),
                'approved' => Verification::query()->where('status', 'approved')->count(),
                'rejected' => Verification::query()->where('status', 'rejected')->count(),
            ],
        ]);
    }

    public function show(Verification $verification): View
    {
        Gate::authorize('manage', \App\Models\User::class);

        $verification->load(['user.profile', 'user.education', 'user.occupation', 'reviewer']);

        return view('backend.verification.show', [
            'verification' => $verification,
            'user' => $verification->user,
            'statuses' => Reference::verificationStatuses(),
        ]);
    }

    /**
     * Stream the private identity document — admins only, never publicly cached.
     */
    public function document(Verification $verification): StreamedResponse
    {
        Gate::authorize('manage', \App\Models\User::class);

        abort_unless($verification->document_path && Storage::disk('local')->exists($verification->document_path), 404);

        return Storage::disk('local')->response($verification->document_path);
    }

    public function decide(VerificationDecisionRequest $request, Verification $verification): RedirectResponse
    {
        Gate::authorize('manage', \App\Models\User::class);

        $decision = $request->validated('decision');
        $note = $request->validated('note');

        match ($decision) {
            'approved' => $this->verifications->approve($verification, $request->user(), $note),
            'rejected' => $this->verifications->reject($verification, $request->user(), $note),
            default => $verification->forceFill([
                'status' => 'pending',
                'admin_note' => $note,
                'reviewed_at' => null,
                'reviewed_by' => $request->user()->id,
            ])->save(),
        };

        return back()->with('success', 'Verification decision recorded.');
    }

    public function destroy(Verification $verification): RedirectResponse
    {
        Gate::authorize('manage', \App\Models\User::class);

        Storage::disk('local')->delete($verification->document_path);
        $verification->delete();

        return back()->with('success', 'Verification record deleted.');
    }
}
