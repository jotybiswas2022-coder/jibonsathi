@extends('frontend.layouts.member')

@section('title', 'Messages')

@section('member-content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Messages</h1>
        </div>
    </div>

    @if (! $canSend)
        <x-frontend::alert :tone="'warning'">
            You can't send messages in this conversation right now.
        </x-frontend::alert>
    @endif

    @include('frontend.messages.partials.sidebar', [
        'conversations' => $conversations,
        'active' => $active,
        'search' => $search,
    ])

    <div class="chat-panel">
        @if (! $partner)
            <x-frontend::empty-state :icon="'fa-user-slash'" :title="'Chat unavailable'"
                :description="'This conversation is no longer available.'" />
        @else
            <div class="chat-thread">
                <header class="chat-head">
                    <a href="{{ route('profiles.show', $partner) }}" class="chat-partner">
                        <x-frontend::avatar :user="$partner" :size="42" :showOnline="true" />
                        <div>
                            <div class="conv-name">{{ $partner->name }}</div>
                            <div class="text-tiny text-muted">
                                {{ $partner->isOnline() ? 'Online now' : 'Last seen '.$partner->lastSeenLabel() }}
                            </div>
                        </div>
                    </a>
                    <form method="POST" action="{{ route('messages.destroy', $active) }}"
                          data-confirm="Delete this conversation for both of you?">
                        @csrf @method('DELETE')
                        <button class="btn-icon btn-icon-sm text-muted" title="Delete conversation"><i class="fas fa-trash"></i></button>
                    </form>
                </header>

                <div class="chat-messages" data-thread-id="{{ $active->id }}">
                    @php($lastDay = null)
                    @forelse ($messages as $message)
                        @php($day = $message->created_at->toDateString())
                        @if ($day !== $lastDay)
                            <div class="chat-day">{{ $message->created_at->format('l, F j') }}</div>
                            @php($lastDay = $day)
                        @endif
                        @php($mine = $message->sender_id === auth()->id())
                        <div class="bubble {{ $mine ? 'mine' : 'theirs' }}">
                            <span>{{ $message->body }}</span>
                            <span class="b-time">
                                {{ $message->created_at->format('g:i A') }}
                                @if ($mine && $message->read_at) <i class="fas fa-check-double" style="margin-left:2px"></i> @endif
                            </span>
                        </div>
                    @empty
                        <p class="text-muted text-small" style="text-align:center;padding:26px">No messages yet — say hello!</p>
                    @endforelse
                </div>

                @if ($canSend)
                    <form method="POST" action="{{ route('messages.store', $active) }}" class="chat-composer">
                        @csrf
                        <textarea name="body" class="input" rows="1" maxlength="2000" placeholder="Type your message..."
                                  required style="flex:1;border-radius:22px;padding:12px 16px"></textarea>
                        <button type="submit" class="btn btn-primary btn-icon" style="border-radius:50%" title="Send"><i class="fas fa-paper-plane"></i></button>
                    </form>
                @endif
            </div>
        @endif
    </div>
@endsection