@props(['icon' => 'fa-heart-crack', 'title' => 'Nothing here yet', 'message' => null, 'actionUrl' => null, 'actionLabel' => null])

<div class="empty-state">
    <div class="es-ico"><i class="fas {{ $icon }}"></i></div>
    <h3>{{ $title }}</h3>
    @if ($message)<p>{{ $message }}</p>@endif
    @if ($actionUrl)
        <a href="{{ $actionUrl }}" class="btn btn-primary">{{ $actionLabel ?? 'Explore' }}</a>
    @endif
</div>