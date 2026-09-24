@extends('frontend.layouts.member')

@section('title', 'Notifications')

@section('member-content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Notifications</h1>
            <p class="page-sub">
                {{ $unread }} unread notification{{ $unread === 1 ? '' : 's' }}
            </p>
        </div>
        @if ($notifications->isNotEmpty())
            <div style="display:flex;gap:8px">
                <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                    @csrf
                    <button class="btn btn-soft btn-sm"><i class="fas fa-check-double"></i> Mark all read</button>
                </form>
                <form method="POST" action="{{ route('notifications.destroy-all') }}" data-confirm="Clear your entire notification inbox?">
                    @csrf
                    <button class="btn btn-ghost btn-sm text-muted"><i class="fas fa-trash"></i> Clear all</button>
                </form>
            </div>
        @endif
    </div>

    <div class="tabs-card" data-tabs style="margin-bottom:20px">
        <a href="{{ route('notifications.index') }}" data-tab="all" class="tab-item {{ $filter === 'all' ? 'active' : '' }}">All</a>
        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" data-tab="unread" class="tab-item {{ $filter === 'unread' ? 'active' : '' }}">
            Unread @if ($unread > 0) <span class="badge-count">{{ $unread }}</span> @endif
        </a>
    </div>

    @if ($notifications->isEmpty())
        <x-frontend::empty-state :icon="'fa-bell-slash'" :title="'No notifications'"
            :description="'You will see updates about interests, messages and matches here.'" />
    @else
        <div class="notif-list">
            @foreach ($notifications as $notification)
                @php
                    $data = $notification->data;
                    $read = $notification->read_at !== null;
                @endphp
                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                    @csrf
                    <button type="submit" class="notif-row {{ $read ? 'read' : '' }}">
                        <span class="notif-icon tone-{{ $data['tone'] ?? 'primary' }}">
                            <i class="fas fa-{{ $data['icon'] ?? 'bell' }}"></i>
                        </span>
                        <span style="flex:1;min-width:0;text-align:left">
                            <span class="notif-title">{{ $data['title'] ?? 'Notification' }}</span>
                            <span class="notif-msg">{{ $data['message'] ?? '' }}</span>
                            <span class="text-tiny text-muted" style="display:block;margin-top:3px">{{ $notification->created_at->diffForHumans() }}</span>
                        </span>
                        @if (! $read)
                            <span class="notif-dot" style="display:inline-flex"></span>
                        @endif
                    </button>
                </form>
            @endforeach
        </div>
        <div class="mt-4">{{ $notifications->links('frontend.components.pagination') }}</div>
    @endif
@endsection