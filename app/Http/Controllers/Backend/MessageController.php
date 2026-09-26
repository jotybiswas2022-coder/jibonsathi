<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Report;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MessageController extends Controller
{
    /**
     * The scopes the tile row filters by. Kept as a constant so the blade and
     * the query cannot drift apart.
     *
     * @var array<int, string>
     */
    private const SCOPES = ['all', 'flagged', 'week', 'empty'];

    /**
     * Oversight only — admins can review reported threads but never send messages.
     */
    public function index(Request $request): View
    {
        Gate::authorize('manage', User::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'scope' => ['nullable', 'in:all,flagged,week,empty'],
            // Kept so older links to ?only_flagged=1 still land on the flagged view.
            'only_flagged' => ['nullable', 'boolean'],
        ]);

        $term = trim((string) ($filters['q'] ?? ''));

        $scope = $filters['scope'] ?? null;
        if ($scope === null && $request->boolean('only_flagged')) {
            $scope = 'flagged';
        }
        $scope = in_array($scope, self::SCOPES, true) ? $scope : 'all';

        $conversations = Conversation::query()
            // primaryPhoto is the relation the rows actually read; loading
            // "profile" instead left one query per participant per page. Only
            // the open reports are pulled in, so this is nearly always empty.
            ->with([
                'userOne.primaryPhoto',
                'userTwo.primaryPhoto',
                'latestMessage.sender',
                'userOne.reportsReceived' => fn ($q) => $q->whereIn('status', self::openReportStatuses()),
                'userTwo.reportsReceived' => fn ($q) => $q->whereIn('status', self::openReportStatuses()),
            ])
            ->withCount('messages')
            ->when($term !== '', fn ($q) => $q->where(function ($inner) use ($term) {
                $like = "%{$term}%";
                $inner->whereHas('userOne', fn ($u) => $u->where('name', 'like', $like)->orWhere('email', 'like', $like))
                    ->orWhereHas('userTwo', fn ($u) => $u->where('name', 'like', $like)->orWhere('email', 'like', $like));
            }))
            ->when($scope === 'flagged', fn ($q) => $q->where(fn ($inner) => $inner
                ->whereHas('userOne.reportsReceived', fn ($r) => $r->whereIn('status', self::openReportStatuses()))
                ->orWhereHas('userTwo.reportsReceived', fn ($r) => $r->whereIn('status', self::openReportStatuses()))))
            ->when($scope === 'week', fn ($q) => $q->where('last_message_at', '>=', now()->subDays(7)))
            ->when($scope === 'empty', fn ($q) => $q->whereDoesntHave('messages'))
            ->orderByDesc('last_message_at')
            // Threads with no messages all share a null timestamp, so the id
            // keeps the order stable and stops rows drifting between pages.
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('backend.messages.index', [
            'conversations' => $conversations,
            'filters' => $filters,
            'scope' => $scope,
            'term' => $term,
            'counts' => $this->scopeCounts(),
        ]);
    }

    public function show(Conversation $conversation): View
    {
        Gate::authorize('manage', User::class);

        $conversation->load([
            'userOne.primaryPhoto',
            'userTwo.primaryPhoto',
            'userOne.reportsReceived' => fn ($q) => $q->whereIn('status', self::openReportStatuses()),
            'userTwo.reportsReceived' => fn ($q) => $q->whereIn('status', self::openReportStatuses()),
        ]);

        // The thread draws an avatar beside every bubble, so the senders and
        // their photos come along with the page instead of one query per row.
        $messages = $conversation->messages()
            ->with(['sender.primaryPhoto'])
            ->paginate(50);

        return view('backend.messages.show', [
            'conversation' => $conversation,
            'messages' => $messages,
            'totalMessages' => $conversation->messages()->count(),
        ]);
    }

    /**
     * @return array<int, string>
     */
    private static function openReportStatuses(): array
    {
        return [Report::STATUS_PENDING, Report::STATUS_INVESTIGATING];
    }

    /**
     * @return array<string, int>
     */
    private function scopeCounts(): array
    {
        $statuses = self::openReportStatuses();

        return [
            'all' => Conversation::query()->count(),
            'flagged' => Conversation::query()
                ->where(fn ($inner) => $inner
                    ->whereHas('userOne.reportsReceived', fn ($r) => $r->whereIn('status', $statuses))
                    ->orWhereHas('userTwo.reportsReceived', fn ($r) => $r->whereIn('status', $statuses)))
                ->count(),
            'week' => Conversation::query()->where('last_message_at', '>=', now()->subDays(7))->count(),
            'empty' => Conversation::query()->whereDoesntHave('messages')->count(),
        ];
    }
}
