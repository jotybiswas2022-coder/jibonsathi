<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\Message\StoreMessageRequest;
use App\Models\Conversation;
use App\Models\User;
use App\Services\ConversationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MessageController extends Controller
{
    public function __construct(private ConversationService $conversations)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $search = $request->string('q')->toString() ?: null;

        $conversations = $this->conversations->listFor($user, $search);

        return view('frontend.messages.index', [
            'conversations' => $conversations,
            'active' => null,
            'search' => $search,
            'unreadTotal' => $this->conversations->unreadCount($user),
        ]);
    }

    public function show(Request $request, Conversation $conversation): View
    {
        Gate::authorize('view', $conversation);

        $user = $request->user();
        $this->conversations->markRead($conversation, $user);

        $conversation->load(['messages.sender.primaryPhoto', 'userOne.primaryPhoto', 'userTwo.primaryPhoto']);

        $partner = $conversation->partnerFor($user->id);

        return view('frontend.messages.show', [
            'conversations' => $this->conversations->listFor($user),
            'active' => $conversation,
            'partner' => $partner,
            'messages' => $conversation->messages,
            'unreadTotal' => 0,
            'canSend' => $partner !== null && ! $user->blocksWith($partner),
            'search' => null,
        ]);
    }

    public function start(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('message', $user);

        $conversation = $this->conversations->start($request->user(), $user);

        return redirect()->route('messages.show', $conversation);
    }

    public function store(StoreMessageRequest $request, Conversation $conversation): RedirectResponse|JsonResponse
    {
        Gate::authorize('send', $conversation);

        $message = $this->conversations->send($conversation, $request->user(), $request->validated('body'));

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'sent',
                'message' => [
                    'id' => $message->id,
                    'body' => $message->body,
                    'sent_at' => $message->created_at->format('g:i A'),
                    'mine' => true,
                ],
            ], 201);
        }

        return redirect()->route('messages.show', $conversation);
    }

    public function destroy(Request $request, Conversation $conversation): RedirectResponse
    {
        Gate::authorize('delete', $conversation);

        $this->conversations->delete($conversation, $request->user());

        return redirect()
            ->route('messages.index')
            ->with('success', 'Conversation deleted.');
    }
}
