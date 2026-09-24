@php($settings = \App\Models\SiteSetting::all_cached())
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $settings['site_name'] ?? 'Jora'){{ isset($settings['seo_title']) && !empty($settings['seo_title']) ? ' — '.$settings['seo_title'] : ' — Bringing Two Lives Together.' }}</title>
    <meta name="description" content="@yield('meta_description', $settings['seo_description'] ?? 'Jora — a free matrimony platform for meaningful connections and genuine profiles.')">
    <link rel="icon" href="{{ $settings['favicon_path'] ?? '' ? \App\Support\Media::url($settings['favicon_path']) : 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>❤️</text></svg>' }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @stack('styles')
</head>
<body class="@yield('bodyclass')">
    <x-frontend::toasts />
    @include('frontend.components.navbar')

    <main>
        @yield('content')
    </main>

    @if (($showFooter ?? true) === true)
        @include('frontend.components.footer')
    @endif

    @auth
        @include('frontend.components.bottom-nav')
    @endauth

    <script src="{{ asset('assets/frontend/js/app.js') }}"></script>
    <script>
        window.Jora = window.Jora || {};
        @auth
        window.Jora.notifyUnread = {{ auth()->user()->unreadNotifications()->count() }};
        @endauth
    </script>
    @stack('scripts')
</body>
</html>