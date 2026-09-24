@props(['conversations', 'active', 'search'])

<aside class="conv-list">
    <form method="GET" action="{{ route('messages.index') }}" class="conv-search">
        <i class="fas fa-search"></i>
        <input type="text" name="q" value="{{ $search }}" placeholder="Search conversations..." class="input">
    </form>

    <div class="conv-items">
        @forelse ($conversations as $conversation)
            @php($partner = $conversation->partnerFor(auth()->id()))
            @php($unread = $conversation->unreadCountFor(auth()->id()))
            <a href="{{ route('messages.show', $conversation) }}"
               class="conv-item {{ $active && $active->id === $conversation->id ? 'active' : '' }}">
                @if ($partner)
                    <x-frontend::avatar :user="$partner" :size="46" :showOnline="true" />
                @endif
                <div style="flex:1;min-width:0">
                    <div class="conv-name">
                        {{ $partner?->name ?? 'Unknown' }}
                        @if ($unread > 0) <span class="badge-count" style="margin-left:6px">{{ $unread }}</span> @endif
                    </div>
                    <div class="conv-preview">
                        @if ($conversation->latestMessage)
                            <span class="@if($unread > 0) unread @endif">
                                @if ($conversation->latestMessage->sender_id === auth()->id()) You: @endif
                                {{ \Illuminate\Support\Str::limit($conversation->latestMessage->body, 40) }}
                            </span>
                        @else
                            <span class="text-muted">Say hello...</span>
                        @endif
                    </div>
                </div>
                @if ($conversation->last_message_at)
                    <span class="conv-time">{{ $conversation->last_message_at->diffForHumans() }}</span>
                @endif
            </a>
        @empty
            <p class="text-muted text-small" style="padding:18px;text-align:center">No conversations found.</p>
        @endforelse
    </div>
</aside>