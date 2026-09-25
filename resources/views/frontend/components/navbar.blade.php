@php($settings = \App\Models\SiteSetting::all_cached())
@php($siteName = $settings['site_name'] ?? 'Jibon Sathi')
<header class="navbar">
    <span class="navbar-ribbon" aria-hidden="true"></span>

    <div class="container navbar-inner">
        <a href="{{ route('home') }}" class="brand">
            <span class="brand-mark"><i class="fas fa-heart"></i></span>
            <span class="brand-text">{{ $siteName }}</span>
        </a>

        <nav class="nav-links">
            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                <i class="fas fa-house"></i> Home
            </a>
            <a href="{{ route('discover.index') }}" class="nav-link {{ request()->routeIs('discover.index') ? 'active' : '' }}">
                <i class="fas fa-magnifying-glass"></i> Discover
            </a>
            @auth
                <a href="{{ route('matches.index') }}" class="nav-link {{ request()->routeIs('matches.index') ? 'active' : '' }}">
                    <i class="fas fa-heart-circle-check"></i> Matches
                </a>
            @endauth
            <a href="{{ route('pages.how-it-works') }}" class="nav-link {{ request()->routeIs('pages.how-it-works') ? 'active' : '' }}">
                <i class="fas fa-compass"></i> How It Works
            </a>
            <a href="{{ route('success-stories.index') }}" class="nav-link {{ request()->routeIs('success-stories.*') ? 'active' : '' }}">
                <i class="fas fa-briefcase"></i> Success Stories
            </a>

            {{-- Guests: shown only inside the opened mobile menu. The desktop CTAs
                 live in .nav-actions — keeping a single copy of each. --}}
            @guest
                <div class="nav-cta-mobile">
                    <a href="{{ route('login') }}" class="btn btn-brand-outline">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Create Profile</a>
                </div>
            @endguest
        </nav>

        <div class="nav-actions">
            @auth
                <a href="{{ route('notifications.index') }}" class="btn-icon notif-btn" aria-label="Notifications">
                    <i class="far fa-bell"></i>
                    <span class="notif-dot" data-unread-url="{{ route('notifications.unread-count') }}"
                        style="{{ auth()->user()->unreadNotifications()->count() ? '' : 'display:none' }}">
                        {{ auth()->user()->unreadNotifications()->count() > 99 ? '99+' : auth()->user()->unreadNotifications()->count() }}
                    </span>
                </a>

                <div class="dropdown" data-dropdown>
                    <button class="nav-avatar" data-dropdown-toggle aria-label="Account menu" style="border:0;background:none;cursor:pointer;padding:0">
                        <x-frontend::avatar :user="auth()->user()" size="md" />
                    </button>
                    <div class="dropdown-menu">
                        <div class="dropdown-item row gap-2" style="pointer-events:none;margin-bottom:4px">
                            <div class="flex flex-col">
                                <strong style="font-size:14px">{{ auth()->user()->name }}</strong>
                                <span class="text-small text-muted">{{ auth()->user()->genderLabel() }}</span>
                            </div>
                        </div>
                        <div class="dropdown-sep"></div>
                        <a href="{{ route('dashboard') }}" class="dropdown-item"><i class="fas fa-gauge-high"></i> Dashboard</a>
                        <a href="{{ route('discover.index') }}" class="dropdown-item"><i class="fas fa-magnifying-glass"></i> Discover Profiles</a>
                        <a href="{{ route('messages.index') }}" class="dropdown-item"><i class="fas fa-comments"></i> Messages</a>
                        <a href="{{ route('verification.index') }}" class="dropdown-item"><i class="fas fa-shield-halved"></i> Verification</a>
                        <a href="{{ route('settings.profile') }}" class="dropdown-item"><i class="fas fa-gear"></i> Settings</a>
                        <div class="dropdown-sep"></div>
                        <form method="POST" action="{{ route('logout') }}" id="js-logout-form">
                            @csrf
                        </form>
                        <a href="#" class="dropdown-item danger" onclick="event.preventDefault(); document.getElementById('js-logout-form').submit();">
                            <i class="fas fa-right-from-bracket"></i> Logout
                        </a>
                    </div>
                </div>
            @else
                <div class="nav-cta-desktop">
                    <a href="{{ route('login') }}" class="btn btn-brand-outline btn-sm">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm btn-shine">Create Profile</a>
                </div>
            @endauth

            <button class="btn-icon nav-toggle" data-nav-toggle aria-label="Toggle menu" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
</header>
