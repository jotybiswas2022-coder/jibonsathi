@props(['tone' => 'brand', 'icon' => null, 'label' => null])

<span class="badge badge-{{ $tone }}">
    @if ($icon)<i class="fas {{ $icon }}"></i>@endif
    {{ $label ?? $slot }}
</span>