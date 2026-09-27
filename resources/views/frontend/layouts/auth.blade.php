@php($settings = \App\Models\SiteSetting::all_cached())
@php($siteName = $settings['site_name'] ?? 'Jibon Sathi')
@php($membersCount = \App\Models\User::query()->active()->count())
@php($onlineCount = \App\Models\User::query()->active()->where('last_active_at', '>', now()->subMinutes(15))->count())
@php($verifiedCount = \App\Models\Profile::query()->where('verification_status', 'verified')->count())
@php($storiesCount = \App\Models\SuccessStory::query()->published()->count())
@php($story = \App\Models\SuccessStory::query()->published()->orderByDesc('is_featured')->orderBy('sort_order')->orderByDesc('id')->first())
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $siteName) · {{ $siteName }}</title>
    <link rel="icon" href="{{ $settings['favicon_path'] ?? '' ? \App\Support\Media::url($settings['favicon_path']) : 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>❤️</text></svg>' }}">
    <style>
    {!! file_get_contents(public_path('assets/frontend/css/app.css')) !!}
</style>
    <script>
        // Reveal-on-scroll stays opt-in so content can never be stuck invisible.
        (function () {
            var root = document.documentElement;
            root.classList.add('reveal-ready');
            window.setTimeout(function () {
                if (root.getAttribute('data-reveal-init') !== '1') {
                    root.classList.remove('reveal-ready');
                }
            }, 2500);
        })();
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @stack('styles')
</head>
<body>
    <x-frontend::toasts />

    <div class="auth-cols">
        <div class="auth-brand-panel">
            {{-- Decoration only. The panel is a wide dark rectangle, and a gradient
                 alone left the right-hand half of every laptop empty. --}}
            <span class="auth-panel-glow" aria-hidden="true"></span>
            <span class="auth-panel-rings" aria-hidden="true"></span>
            <span class="auth-panel-dots" aria-hidden="true"></span>

            <a href="{{ route('home') }}" class="brand">
                <span class="brand-mark gold"><i class="fas fa-heart"></i></span>
                {{ $siteName }}
            </a>

            <div class="auth-panel-body">
                <div class="auth-panel-lead">
                    <span class="auth-brand-eyebrow"><i class="fas fa-heart"></i> Free matrimony · Bangladesh</span>
                    <h2>{{ $settings['hero_headline'] ?? 'Find Someone Who Complements Your Life' }}</h2>
                    <p>{{ $settings['hero_subheading'] ?? 'Meaningful connections, genuine profiles, and a better way to find your life partner.' }}</p>

                    @if ($onlineCount > 0)
                        <span class="auth-panel-live">
                            <i class="fa-solid fa-circle" aria-hidden="true"></i>
                            {{ number_format($onlineCount) }} {{ \Illuminate\Support\Str::plural('member', $onlineCount) }} online now
                        </span>
                    @endif

                    <ul class="auth-panel-trust">
                        <li><i class="fas fa-gift"></i> Free forever, no hidden charges</li>
                        <li><i class="fas fa-user-shield"></i> Your details stay private</li>
                    </ul>
                </div>

                <div class="auth-panel-aside">
                    <div class="auth-brand-stats">
                        <div class="stat">
                            <span class="ico"><i class="fas fa-users"></i></span>
                            <span class="body">
                                <span class="num">{{ number_format($membersCount) }}</span>
                                <span class="lbl">Members</span>
                            </span>
                        </div>
                        <div class="stat">
                            <span class="ico"><i class="fas fa-badge-check"></i></span>
                            <span class="body">
                                <span class="num">{{ number_format($verifiedCount) }}</span>
                                <span class="lbl">Verified profiles</span>
                            </span>
                        </div>
                        <div class="stat">
                            <span class="ico"><i class="fas fa-ring"></i></span>
                            <span class="body">
                                <span class="num">{{ number_format($storiesCount) }}</span>
                                <span class="lbl">Success stories</span>
                            </span>
                        </div>
                    </div>

                    {{-- A real couple from the site's own stories, so the panel earns
                         the space it takes instead of sitting as a flat gradient. --}}
                    @if ($story && filled($story->story))
                        <figure class="auth-story">
                            <span class="pic">
                                @if ($story->photoUrl())
                                    <img src="{{ $story->photoUrl() }}" alt="{{ $story->coupleLabel() }}" loading="lazy" decoding="async">
                                @else
                                    <span class="initials">{{ $story->initials() }}</span>
                                @endif
                            </span>
                            <span class="body">
                                <blockquote>{{ \Illuminate\Support\Str::limit($story->story, 132) }}</blockquote>
                                <figcaption>
                                    <span class="names">{{ $story->coupleLabel() }}</span>
                                    <span class="meta">
                                        @if ($story->location)<i class="fas fa-location-dot"></i> {{ $story->location }}@endif
                                        @if ($story->married_on)<i class="fas fa-calendar-heart"></i> {{ $story->married_on->format('M Y') }}@endif
                                    </span>
                                </figcaption>
                            </span>
                        </figure>
                    @endif
                </div>
            </div>
        </div>

        <div class="auth-form-panel">
            <div class="auth-card">
                @yield('auth-content')
            </div>
        </div>
    </div>

    <script>
    {!! file_get_contents(public_path('assets/frontend/js/app.js')) !!}
    </script>
    @stack('scripts')
</body>
</html>