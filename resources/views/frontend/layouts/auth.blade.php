@php($settings = \App\Models\SiteSetting::all_cached())
@php($siteName = $settings['site_name'] ?? 'Jibon Sathi')
@php($membersCount = \App\Models\User::query()->active()->count())
@php($storiesCount = \App\Models\SuccessStory::query()->published()->count())
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @stack('styles')
</head>
<body>
    <x-frontend::toasts />

    <div class="auth-cols">
        <div class="auth-brand-panel">
            <a href="{{ route('home') }}" class="brand">
                <span class="brand-mark gold"><i class="fas fa-heart"></i></span>
                {{ $siteName }}
            </a>
            <div style="margin-top:auto">
                <h2>{{ $settings['hero_headline'] ?? 'Find Someone Who Complements Your Life' }}</h2>
                <p>{{ $settings['hero_subheading'] ?? 'Meaningful connections, genuine profiles, and a better way to find your life partner.' }}</p>
                <div class="auth-brand-stats">
                    <div>
                        <div class="num">{{ number_format($membersCount) }}</div>
                        <div class="lbl">Members</div>
                    </div>
                    <div>
                        <div class="num">{{ number_format($storiesCount) }}</div>
                        <div class="lbl">Success Stories</div>
                    </div>
                    <div>
                        <div class="num">100%</div>
                        <div class="lbl">Free Forever</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="auth-form-panel">
            <div class="auth-card">
                @yield('auth-content')
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/frontend/js/app.js') }}?v=2"></script>
    @stack('scripts')
</body>
</html>