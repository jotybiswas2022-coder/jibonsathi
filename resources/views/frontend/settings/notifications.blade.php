@extends('frontend.layouts.member')

@section('title', 'Notification Settings')

@section('member-content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Settings</h1>
            <p class="page-sub">Choose what we notify you about.</p>
        </div>
    </div>

    @include('frontend.settings.partials.nav', ['active' => 'notifications'])

    <div class="card card-pad" style="max-width:860px">
        <form method="POST" action="{{ route('settings.notifications.update') }}">
            @csrf
            @method('PUT')

            <div class="switch-field">
                <label class="switch-row">
                    <input type="checkbox" name="notify_interests" value="1" @checked(old('notify_interests', $user->profile?->notify_interests ?? true))>
                    <span class="switch"></span>
                    <span><strong>Interest requests</strong><br><span class="text-tiny text-muted">When a member sends you an interest.</span></span>
                </label>
            </div>

            <div class="switch-field">
                <label class="switch-row">
                    <input type="checkbox" name="notify_messages" value="1" @checked(old('notify_messages', $user->profile?->notify_messages ?? true))>
                    <span class="switch"></span>
                    <span><strong>Messages</strong><br><span class="text-tiny text-muted">When you receive a new message.</span></span>
                </label>
            </div>

            <div class="switch-field">
                <label class="switch-row">
                    <input type="checkbox" name="notify_profile_views" value="1" @checked(old('notify_profile_views', $user->profile?->notify_profile_views ?? true))>
                    <span class="switch"></span>
                    <span><strong>Profile views</strong><br><span class="text-tiny text-muted">When members view your profile.</span></span>
                </label>
            </div>

            <div class="switch-field">
                <label class="switch-row">
                    <input type="checkbox" name="notify_shortlists" value="1" @checked(old('notify_shortlists', $user->profile?->notify_shortlists ?? true))>
                    <span class="switch"></span>
                    <span><strong>Shortlists</strong><br><span class="text-tiny text-muted">When a member shortlists your profile.</span></span>
                </label>
            </div>

            <button class="btn btn-primary">Save Changes</button>
        </form>
    </div>

    @if ($recent->isNotEmpty())
        <div class="card card-pad" style="max-width:860px;margin-top:20px">
            <h3 class="card-title"><i class="fas fa-bell"></i> Recent Notifications</h3>
            <div class="vlist">
                @foreach ($recent as $notification)
                    <div class="vlist-row">
                        <span class="notif-icon tone-{{ $notification->data['tone'] ?? 'primary' }}" style="width:36px;height:36px">
                            <i class="fas fa-{{ $notification->data['icon'] ?? 'bell' }}"></i>
                        </span>
                        <div style="flex:1;min-width:0">
                            <div class="vlist-name">{{ $notification->data['title'] ?? 'Notification' }}</div>
                            <div class="text-tiny text-muted">{{ $notification->data['message'] ?? '' }}</div>
                        </div>
                        <span class="time-ago">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
            <a href="{{ route('notifications.index') }}" class="text-small" style="color:var(--brand);font-weight:600">View all notifications</a>
        </div>
    @endif
@endsection