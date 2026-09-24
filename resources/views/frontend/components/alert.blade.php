@props(['tone' => 'info', 'title' => null, 'autoClose' => false])

@php
    $icons = ['success' => 'fa-circle-check', 'danger' => 'fa-circle-exclamation', 'warning' => 'fa-triangle-exclamation', 'info' => 'fa-circle-info'];
    $icon = $icons[$tone] ?? $icons['info'];
    $children = trim($slot = $slot ?? '');
@endphp

<div class="alert alert-{{ $tone }}" @if ($autoClose) data-auto-close @endif>
    <i class="fas {{ $icon }}"></i>
    <div>
        @if ($title)<strong>{{ $title }}</strong>@endif
        <p>{{ $slot }}</p>
    </div>
</div>