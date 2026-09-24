@extends('frontend.layouts.member')

@section('title', 'Messages')

@section('member-content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Messages</h1>
            <p class="page-sub">
                @if ($unreadTotal > 0)
                    You have <strong>{{ $unreadTotal }}</strong> unread message{{ $unreadTotal === 1 ? '' : 's' }}.
                @else
                    You're all caught up.
                @endif
            </p>
        </div>
    </div>

    @include('frontend.messages.partials.sidebar', [
        'conversations' => $conversations,
        'active' => $active,
        'search' => $search,
    ])

    <div class="chat-panel">
        @if ($conversations->isEmpty())
            <x-frontend::empty-state :icon="'fa-comments'" :title="'No conversations yet'"
                :description="'Once an interest is accepted you can start chatting with that member here.'" />
        @else
            <div class="chat-empty">
                <div class="es-ico"><i class="fas fa-comment-dots"></i></div>
                <p class="text-muted">Select a conversation to start reading.</p>
            </div>
        @endif
    </div>
@endsection