@php($user = auth()->user())
@php($unread = $user?->unreadNotifications()->count() ?? 0)
@php($activeTab = request()->routeIs('messages.*') ? 'messages' : (request()->routeIs('favorites.*') ? 'favorites' : 'discover'))
<nav class="bottom-nav">
    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="fas fa-house"></i> Home
    </a>
    <a href="{{ route('discover.index') }}" class="{{ request()->routeIs('discover.index') || request()->routeIs('profiles.show') ? 'active' : '' }}">
        <i class="fas fa-magnifying-glass"></i> Discover
    </a>
    <a href="{{ route('matches.index') }}" class="{{ request()->routeIs('matches.index') || request()->routeIs('interests.*') ? 'active' : '' }}">
        <i class="fas fa-heart-circle-plus"></i> Matches
    </a>
    <a href="{{ route('messages.index') }}" class="{{ request()->routeIs('messages.*') ? 'active' : '' }}">
        <span class="bn-dot"><i class="fas fa-comments"></i></span> Messages
    </a>
    <a href="{{ route('settings.profile') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
        <i class="fas fa-user"></i> Profile
    </a>
</nav>