@props(['active'])

@php
    $links = [
        'profile' => ['route' => 'settings.profile', 'label' => 'Profile', 'icon' => 'fa-id-card'],
        'career' => ['route' => 'settings.career', 'label' => 'Education & Career', 'icon' => 'fa-graduation-cap'],
        'family' => ['route' => 'settings.family', 'label' => 'Family', 'icon' => 'fa-house-user'],
        'lifestyle' => ['route' => 'settings.lifestyle', 'label' => 'Lifestyle', 'icon' => 'fa-spa'],
        'preference' => ['route' => 'settings.preference', 'label' => 'Partner Preference', 'icon' => 'fa-bullseye'],
        'photos' => ['route' => 'settings.photos', 'label' => 'Photos', 'icon' => 'fa-images'],
        'privacy' => ['route' => 'settings.privacy', 'label' => 'Privacy', 'icon' => 'fa-shield-halved'],
        'notifications' => ['route' => 'settings.notifications', 'label' => 'Notifications', 'icon' => 'fa-bell'],
        'password' => ['route' => 'settings.password', 'label' => 'Password', 'icon' => 'fa-key'],
        'account' => ['route' => 'settings.account', 'label' => 'Account', 'icon' => 'fa-user-gear'],
        'blocked' => ['route' => 'settings.blocked', 'label' => 'Blocked Users', 'icon' => 'fa-ban'],
    ];
@endphp

<div class="settings-nav">
    @foreach ($links as $key => $link)
        <a href="{{ route($link['route']) }}" class="settings-nav-item {{ $active === $key ? 'active' : '' }}">
            <i class="fas {{ $link['icon'] }}"></i> {{ $link['label'] }}
        </a>
    @endforeach
</div>