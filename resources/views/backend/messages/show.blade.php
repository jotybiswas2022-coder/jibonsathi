@extends('backend.layouts.app')

@section('title', 'Conversation Thread')
@section('crumb', 'Messages · Read-only oversight')

@section('content')
    <a href="{{ route('backend.messages.index') }}" class="btn btn-ghost btn-sm mb-3" style="margin-bottom:14px"><i class="fas fa-arrow-left"></i> Back to conversations</a>

    <div class="card">
        <div class="card-head">
            <h3>
                {{ $conversation->userOne?->name ?? 'Deleted' }}
                <span class="text-muted" style="font-weight:400">&amp;</span>
                {{ $conversation->userTwo?->name ?? 'Deleted' }}
            </h3>
            <div class="flex gap-2">
                @if ($conversation->userOne)
                    <a href="{{ route('backend.users.show', $conversation->userOne) }}" class="btn btn-outline btn-sm"><i class="fas fa-user"></i> {{ $conversation->userOne->name }}</a>
                @endif
                @if ($conversation->userTwo)
                    <a href="{{ route('backend.users.show', $conversation->userTwo) }}" class="btn btn-outline btn-sm"><i class="fas fa-user"></i> {{ $conversation->userTwo->name }}</a>
                @endif
            </div>
        </div>
        <div class="chat-window" style="height:600px">
            <div class="chat-log" style="align-items:flex-start">
                @forelse ($messages as $m)
                    <div class="msg-line" style="{{ $messages->currentPage() > 1 ? '' : '' }}">
                        <div class="m-sender">{{ $m->sender?->name ?? 'Deleted member' }}</div>
                        <div class="msg-bubble">{{ $m->body }}<span class="m-time">{{ $m->created_at?->format('d M Y, g:i A') }}</span></div>
                    </div>
                @empty
                    <div class="empty-state" style="margin:auto">
                        <i class="fas fa-comment-slash"></i>
                        <h3>No messages</h3>
                        <p>This thread is empty.</p>
                    </div>
                @endforelse
            </div>
        </div>
        @if ($messages->hasPages())
            <div style="padding:14px;border-top:1px solid var(--border)">{{ $messages->links('backend.components.pagination') }}</div>
        @endif
    </div>
@endsection