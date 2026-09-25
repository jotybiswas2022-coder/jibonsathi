@props(['icon' => 'fa-heart-crack', 'title' => 'Nothing here yet', 'message' => null, 'description' => null, 'actionUrl' => null, 'actionLabel' => null])

@php($copy = $message ?? $description)

<div class="empty-state">
    <div class="es-ico"><i class="fas {{ $icon }}"></i></div>
    <h3>{{ $title }}</h3>
    @if ($copy)<p>{{ $copy }}</p>@endif
    @if ($actionUrl)
        <a href="{{ $actionUrl }}" class="btn btn-primary">{{ $actionLabel ?? 'Explore' }}</a>
    @endif
</div>