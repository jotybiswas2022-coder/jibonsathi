@php($u = auth()->user())
@php($pendingInterests = \App\Models\Interest::query()->where('receiver_id', $u->id)->where('status', 'pending')->count())
@php($unreadMsgs = \App\Models\Message::query()->where('read_at', null)
    ->where('sender_id', '!=', $u->id)
    ->whereHas('conversation', fn ($q) => $q->where('user_one_id', $u->id)->orWhere('user_two_id', $u->id))
    ->count())

<div class="side-profile">
    <x-frontend::avatar :user="$u" size="lg" :showOnline="true" />
    <div class="min-width-0 flex-1">
        <div class="sp-name">{{ $u->name }}</div>
        <div class="sp-sub">{{ $u->genderLabel() }} · {{ $u->completion() }}% complete</div>
    </div>
</div>

<div class="side-label">Discover</div>
<a href="{{ route('dashboard') }}" class="side-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
    <i class="fas fa-gauge-high"></i> Dashboard
</a>
<a href="{{ route('discover.index') }}" class="side-item {{ request()->routeIs('discover.index') || request()->routeIs('profiles.show') ? 'active' : '' }}">
    <i class="fas fa-magnifying-glass"></i> Discover
</a>
<a href="{{ route('matches.index') }}" class="side-item {{ request()->routeIs('matches.index') ? 'active' : '' }}">
    <i class="fas fa-heart-circle-check"></i> Matches
</a>

<div class="side-label">Connect</div>
<a href="{{ route('interests.received') }}" class="side-item {{ request()->routeIs('interests.*') ? 'active' : '' }}">
    <i class="fas fa-heart"></i> Interests
    @if ($pendingInterests > 0)<span class="side-badge-count">{{ $pendingInterests }}</span>@endif
</a>
<a href="{{ route('favorites.index') }}" class="side-item {{ request()->routeIs('favorites.index') ? 'active' : '' }}">
    <i class="fas fa-star"></i> Shortlist
</a>
<a href="{{ route('messages.index') }}" class="side-item {{ request()->routeIs('messages.*') ? 'active' : '' }}">
    <i class="fas fa-comments"></i> Messages
    @if ($unreadMsgs > 0)<span class="side-badge-count">{{ $unreadMsgs }}</span>@endif
</a>
<a href="{{ route('notifications.index') }}" class="side-item {{ request()->routeIs('notifications.index') ? 'active' : '' }}">
    <i class="fas fa-bell"></i> Notifications
</a>

<div class="side-label">Account</div>
<a href="{{ route('verification.index') }}" class="side-item {{ request()->routeIs('verification.index') ? 'active' : '' }}">
    <i class="fas fa-shield-halved"></i> Verification
</a>
<a href="{{ route('reports.index') }}" class="side-item {{ request()->routeIs('reports.index') ? 'active' : '' }}">
    <i class="fas fa-flag"></i> My Reports
</a>
<a href="{{ route('settings.profile') }}" class="side-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
    <i class="fas fa-gear"></i> Settings
</a>