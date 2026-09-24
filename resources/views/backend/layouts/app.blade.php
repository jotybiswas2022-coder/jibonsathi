@php($admin = auth()->user())
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Jora Admin</title>
    <link rel="stylesheet" href="/assets/backend/css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<body>
    @include('frontend.components.toasts')

    <div class="admin-layout">
        @include('backend.partials.sidebar')

        <div class="admin-main">
            <header class="admin-topbar">
                <div>
                    <h1>@yield('title', 'Dashboard')</h1>
                    <div class="crumb">@yield('crumb', 'Jora Control Panel')</div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="btn btn-outline btn-sm" target="_blank" rel="noopener">
                        <i class="fas fa-globe"></i> View Website
                    </a>
                    <form method="POST" action="{{ route('logout') }}" style="margin:0">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm"><i class="fas fa-right-from-bracket"></i> Logout</button>
                    </form>
                </div>
            </header>

            <main class="admin-content">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="/assets/backend/js/app.js"></script>
    @stack('scripts')
</body>
</html>