<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use App\Models\User;
use App\Services\InterestService;
use App\Services\MatchingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InterestController extends Controller
{
    public function __construct(
        private InterestService $interests,
        private MatchingService $matcher,
    ) {
    }

    public function received(Request $request): View
    {
        $user = $request->user();

        $interests = $user->receivedInterests()
            ->with(['sender.profile', 'sender.primaryPhoto', 'sender.education', 'sender.occupation'])
            ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
            ->whereDoesntHave('sender.blocks', fn ($q) => $q->where('blocked_id', $user->id))
            ->paginate(9)
            ->withQueryString();

        return view('frontend.interests.received', [
            'interests' => $interests,
            'scores' => $this->matcher->decorate($interests->getCollection()->pluck('sender'), $user),
            'counts' => $this->counts($user),
        ]);
    }

    public function sent(Request $request): View
    {
        $user = $request->user();

        $interests = $user->sentInterests()
            ->with(['receiver.profile', 'receiver.primaryPhoto', 'receiver.education', 'receiver.occupation'])
            ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
            ->paginate(9)
            ->withQueryString();

        return view('frontend.interests.sent', [
            'interests' => $interests,
            'scores' => $this->matcher->decorate($interests->getCollection()->pluck('receiver'), $user),
            'counts' => $this->counts($user),
        ]);
    }

    public function accepted(Request $request): View
    {
        $user = $request->user();

        $interests = Interest::query()
            ->where('status', Interest::STATUS_ACCEPTED)
            ->where(fn ($q) => $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id))
            ->with([
                'sender.profile', 'sender.primaryPhoto', 'sender.education', 'sender.occupation',
                'receiver.profile', 'receiver.primaryPhoto', 'receiver.education', 'receiver.occupation',
            ])
            ->latest('responded_at')
            ->paginate(9)
            ->withQueryString();

        $partners = $interests->getCollection()->map(fn (Interest $interest) => $interest->otherParty($user->id));

        return view('frontend.interests.accepted', [
            'interests' => $interests,
            'scores' => $this->matcher->decorate($partners, $user),
            'counts' => $this->counts($user),
        ]);
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('sendInterest', $user);

        $this->interests->send($request->user(), $user);

        return back()->with('success', "Interest sent to {$user->name}.");
    }

    public function accept(Request $request, Interest $interest): RedirectResponse
    {
        Gate::authorize('respond', $interest);

        $this->interests->accept($interest, $request->user());

        return back()->with('success', 'Interest accepted — you can chat with '.$interest->sender->name.' now.');
    }

    public function reject(Request $request, Interest $interest): RedirectResponse
    {
        Gate::authorize('respond', $interest);

        $this->interests->reject($interest, $request->user());

        return back()->with('success', 'Interest declined.');
    }

    public function cancel(Request $request, Interest $interest): RedirectResponse
    {
        Gate::authorize('cancel', $interest);

        $this->interests->cancel($interest, $request->user());

        return back()->with('success', 'Interest cancelled.');
    }

    /**
     * @return array<string, int>
     */
    private function counts(User $user): array
    {
        return [
            'received' => $user->receivedInterests()->where('status', Interest::STATUS_PENDING)->count(),
            'sent' => $user->sentInterests()->where('status', Interest::STATUS_PENDING)->count(),
            'accepted' => Interest::query()
                ->where('status', Interest::STATUS_ACCEPTED)
                ->where(fn ($q) => $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id))
                ->count(),
        ];
    }
}
