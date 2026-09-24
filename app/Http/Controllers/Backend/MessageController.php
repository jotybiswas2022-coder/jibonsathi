<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MessageController extends Controller
{
    /**
     * Oversight only — admins can review reported threads but never send messages.
     */
    public function index(Request $request): View
    {
        Gate::authorize('manage', User::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'only_flagged' => ['nullable', 'boolean'],
        ]);

        $conversations = Conversation::query()
            ->with(['userOne.profile', 'userTwo.profile', 'latestMessage'])
            ->when(filled($filters['q'] ?? null), fn ($q) => $q->where(function ($inner) use ($filters) {
                $term = $filters['q'];
                $inner->whereHas('userOne', fn ($u) => $u->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('userTwo', fn ($u) => $u->where('name', 'like', "%{$term}%"));
            }))
            ->when($request->boolean('only_flagged'), fn ($q) => $q->whereHas('userOne.reportsReceived')
                ->orWhereHas('userTwo.reportsReceived'))
            ->orderByDesc('last_message_at')
            ->paginate(20)
            ->withQueryString();

        return view('backend.messages.index', [
            'conversations' => $conversations,
            'filters' => $filters,
            'total' => Conversation::query()->count(),
        ]);
    }

    public function show(Conversation $conversation): View
    {
        Gate::authorize('manage', User::class);

        $conversation->load(['messages.sender', 'userOne.profile', 'userTwo.profile']);

        return view('backend.messages.show', [
            'conversation' => $conversation,
            'messages' => $conversation->messages()->paginate(50),
        ]);
    }
}
