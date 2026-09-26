@extends('backend.layouts.app')

@php
    $one = $conversation->userOne;
    $two = $conversation->userTwo;
    $reports = $conversation->openReportCounts();

    /* Members are shown as cards rather than a table so the thread reads the
       same way on a phone, where a two column table would squeeze. */
    $participants = collect([$one, $two])->map(fn ($member, $side) => [
        'member' => $member,
        'side' => $side === 0 ? 'one' : 'two',
    ]);

    $first = $messages->first();
    $last = $messages->last();
@endphp

@section('title', 'Conversation Thread')
@section('crumb', 'Messages · Read-only oversight')

@section('content')
    <div class="page-head">
        <div class="page-head-text">
            <h2>{{ $conversation->participantsLabel() }}</h2>
            <p>
                {{ $totalMessages }} {{ Str::plural('message', $totalMessages) }} in this thread.
                @if ($first && $last)
                    First sent {{ $first->created_at?->diffForHumans() }}, latest {{ $last->created_at?->diffForHumans() }}.
                @endif
            </p>
        </div>
        <div class="page-head-actions">
            <a href="{{ route('backend.messages.index', array_filter(['scope' => request('scope'), 'q' => request('q')])) }}"
               class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> All conversations
            </a>
        </div>
    </div>

    @if ($reports['one'] > 0 || $reports['two'] > 0)
        <div class="ms-alert">
            <i class="fas fa-triangle-exclamation"></i>
            <div>
                <strong>{{ $reports['one'] + $reports['two'] }} open {{ Str::plural('report', $reports['one'] + $reports['two']) }}</strong>
                on this conversation. Review the {{ $reports['one'] + $reports['two'] > 1 ? 'reports' : 'report' }} before deciding whether the thread needs action.
                <a href="{{ route('backend.reports.index') }}">Open reports</a>
            </div>
        </div>
    @endif

    <div class="ms-participants">
        @foreach ($participants as $participant)
            @php $member = $participant['member']; @endphp
            <div class="card ms-participant">
                <div class="ms-participant-head">
                    <span class="avatar ms-participant-avatar">
                        @if ($member?->primaryPhoto)
                            <img src="{{ $member->primaryPhoto->url() }}" alt="" loading="lazy">
                        @else
                            <span class="initials">{{ $member?->initials ?? 'J' }}</span>
                        @endif
                    </span>
                    <div class="st-cell-text">
                        <div class="ms-participant-name">
                            {{ $member?->name ?? 'Deleted member' }}
                        </div>
                        <div class="cu-sub">{{ $member?->email ?? 'This account no longer exists' }}</div>
                    </div>
                </div>
                <div class="ms-participant-meta">
                    @if ($member)
                        <span class="badge badge-muted">{{ $participant['side'] === 'one' ? 'First member' : 'Second member' }}</span>
                        @if ($member->created_at)
                            <span class="ms-participant-row"><i class="fas fa-calendar"></i> Joined {{ $member->created_at->format('M Y') }}</span>
                        @endif
                        @if ($member->last_active_at)
                            <span class="ms-participant-row"><i class="fas fa-circle"></i> Active {{ $member->last_active_at->diffForHumans() }}</span>
                        @endif
                        @if ($participant['side'] === 'one' && $reports['one'] > 0)
                            <span class="badge badge-danger"><i class="fas fa-flag"></i> {{ $reports['one'] }} open</span>
                        @elseif ($participant['side'] === 'two' && $reports['two'] > 0)
                            <span class="badge badge-danger"><i class="fas fa-flag"></i> {{ $reports['two'] }} open</span>
                        @endif
                    @else
                        <span class="badge badge-muted">Removed account</span>
                    @endif
                </div>
                @if ($member)
                    <a href="{{ route('backend.users.show', $member) }}" class="btn btn-outline btn-sm ms-participant-link">
                        <i class="fas fa-user"></i> View profile
                    </a>
                @endif
            </div>
        @endforeach
    </div>

    <div class="card ms-thread">
        <div class="card-head ms-thread-head">
            <div>
                <h3>Thread</h3>
                <p class="ms-thread-sub">
                    {{ $messages->count() }} {{ Str::plural('message', $messages->count()) }} on this page
                    @if ($messages->hasPages())
                        of {{ $totalMessages }} in total
                    @endif
                </p>
            </div>
            <span class="badge badge-muted"><i class="fas fa-lock"></i> Admins cannot reply</span>
        </div>

        <div class="chat-window ms-log" data-ms-log>
            <div class="chat-log ms-log-inner">
                @php $previousDay = null; @endphp
                @forelse ($messages as $message)
                    @php
                        $fromOne = $conversation->isFromUserOne($message->sender_id);
                        $sender = $message->sender;
                        /* A date rule only when the day changes, tracked with a
                           plain variable rather than by indexing back through
                           the paginator. */
                        $day = $message->created_at?->format('Y-m-d');
                        $newDay = $day !== $previousDay;
                        $previousDay = $day;
                    @endphp
                    @if ($newDay)
                        <div class="ms-day">
                            <span>{{ $message->created_at?->format('l, d M Y') ?? 'Unknown date' }}</span>
                        </div>
                    @endif

                    <div class="ms-msg {{ $fromOne ? 'is-one' : 'is-two' }}">
                        <span class="avatar ms-msg-avatar">
                            @if ($sender?->primaryPhoto)
                                <img src="{{ $sender->primaryPhoto->url() }}" alt="" loading="lazy">
                            @else
                                <span class="initials">{{ $sender?->initials ?? 'J' }}</span>
                            @endif
                        </span>
                        <div class="ms-msg-main">
                            <div class="ms-msg-meta">
                                <span class="ms-msg-who">{{ $sender?->name ?? 'Deleted member' }}</span>
                                <time class="ms-msg-time" datetime="{{ $message->created_at?->toIso8601String() }}">
                                    {{ $message->created_at?->format('d M Y, g:i A') }}
                                </time>
                                @unless ($message->isRead())
                                    <span class="ms-msg-unread" title="Not marked as read by the recipient">
                                        <i class="fas fa-envelope"></i> Unread
                                    </span>
                                @endunless
                            </div>
                            <div class="ms-bubble">{{ $message->body }}</div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state ms-log-empty">
                        <i class="fas fa-comment-slash"></i>
                        <h3>No messages in this thread</h3>
                        <p>These two members have never sent each other a message.</p>
                    </div>
                @endforelse
            </div>
        </div>

        @if ($messages->hasPages())
            <div class="ms-thread-foot">
                {{ $messages->links('backend.components.pagination') }}
            </div>
        @endif
    </div>
@endsection
