<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\VerificationDecisionRequest;
use App\Models\Verification;
use App\Services\VerificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VerificationController extends Controller
{
    public function __construct(private VerificationService $verifications)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in([
                Verification::STATUS_PENDING,
                Verification::STATUS_APPROVED,
                Verification::STATUS_REJECTED,
                'all',
            ])],
            'type' => ['nullable', Rule::in(array_keys(Verification::TYPES))],
            'q' => ['nullable', 'string', 'max:80'],
        ]);

        $status = $filters['status'] ?? Verification::STATUS_PENDING;
        $type = $filters['type'] ?? null;
        $term = trim((string) ($filters['q'] ?? ''));

        $verifications = Verification::query()
            ->with(['user', 'user.primaryPhoto', 'reviewer'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($term !== '', function ($q) use ($term) {
                $like = "%{$term}%";
                $q->where(function ($inner) use ($like) {
                    $inner->where('type', 'like', $like)
                        ->orWhere('document_type', 'like', $like)
                        ->orWhere('note', 'like', $like)
                        ->orWhere('admin_note', 'like', $like)
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $like)->orWhere('email', 'like', $like));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('backend.verification.index', [
            'verifications' => $verifications,
            'status' => $status,
            'type' => $type,
            'term' => $term,
            'types' => Verification::TYPES,
            'counts' => $this->statusCounts(),
            'typeCounts' => $this->typeCounts($status),
            'typeCountsByStatus' => $this->typeCountsByStatus(),
        ]);
    }

    public function show(Verification $verification): View
    {
        Gate::authorize('manage', \App\Models\User::class);

        // The other attempts are rendered as a list, so they are loaded up front
        // rather than counted per row: a member who has been refused twice is the
        // strongest signal on this page.
        $verification->load(['user.primaryPhoto', 'user.education', 'user.occupation', 'reviewer']);

        return view('backend.verification.show', [
            'verification' => $verification,
            'user' => $verification->user,
            'others' => Verification::query()
                ->where('user_id', $verification->user_id)
                ->whereKeyNot($verification->id)
                ->latest()
                ->limit(5)
                ->get(),
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

    /**
     * Per-status totals for the filter tiles, in one query rather than three.
     *
     * @return array<string, int>
     */
    private function statusCounts(): array
    {
        $counts = array_fill_keys([
            Verification::STATUS_PENDING,
            Verification::STATUS_APPROVED,
            Verification::STATUS_REJECTED,
        ], 0);

        Verification::query()
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->get()
            ->each(function ($row) use (&$counts) {
                $counts[$row->status] = (int) $row->total;
            });

        $counts['all'] = array_sum($counts);

        return $counts;
    }

    /**
     * How many verifications sit under each type inside the status currently
     * being viewed, so a chip never promises more rows than the active status
     * holds. One query rather than one per type.
     *
     * @return array<string, int>
     */
    private function typeCounts(string $status): array
    {
        return Verification::query()
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->selectRaw('type, COUNT(*) AS total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->map(fn ($n) => (int) $n)
            ->all();
    }

    /**
     * The same numbers for every status at once, so the chips can be repainted
     * when the status tile is clicked without another round trip. Keyed by status
     * with an "all" column, since the queue is read one status at a time.
     *
     * @return array<string, array<string, int>>
     */
    private function typeCountsByStatus(): array
    {
        $matrix = [];

        Verification::query()
            ->selectRaw('status, type, COUNT(*) AS total')
            ->groupBy('status', 'type')
            ->get()
            ->each(function ($row) use (&$matrix) {
                $matrix[$row->status][$row->type] = (int) $row->total;
                $matrix['all'][$row->type] = ($matrix['all'][$row->type] ?? 0) + (int) $row->total;
            });

        return $matrix;
    }
}
