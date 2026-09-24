@props(['active'])

@php
    $tabs = [
        'received' => ['route' => 'interests.received', 'label' => 'Received', 'icon' => 'fa-inbox'],
        'sent' => ['route' => 'interests.sent', 'label' => 'Sent', 'icon' => 'fa-paper-plane'],
        'accepted' => ['route' => 'interests.accepted', 'label' => 'Accepted', 'icon' => 'fa-heart'],
    ];
@endphp

<div class="tabs-card" data-tabs style="margin-bottom:20px">
    @foreach ($tabs as $key => $tab)
        <a href="{{ route($tab['route']) }}"
           data-tab="{{ $key }}"
           class="tab-item {{ $active === $key ? 'active' : '' }}">
            <i class="fas {{ $tab['icon'] }}"></i> {{ $tab['label'] }}
            @if (isset($counts[$key]) && $counts[$key] > 0)
                <span class="badge-count">{{ $counts[$key] }}</span>
            @endif
        </a>
    @endforeach
</div>