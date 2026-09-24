@props(['user', 'size' => 'md', 'class' => '', 'showOnline' => false])

@php
    $sizeClass = match ($size) { 'sm' => 'avatar-sm', 'md' => '', 'lg' => 'avatar-lg', 'xl' => 'avatar-xl', default => '' };
    $photo = $user->photoUrl();
    $isAvatar = \Str::contains($photo, '/media/avatar/');
@endphp

<span class="avatar {{ $sizeClass }} {{ $class }}">
    @if ($isAvatar)
        <span class="initials">{{ $user->initials ?? mb_strtoupper(mb_substr($user->name, 0, 2)) }}</span>
    @else
        <img src="{{ $photo }}" alt="{{ $user->name }}" loading="lazy">
    @endif
    @if ($showOnline)
        <span class="status-dot {{ $user->isOnline() ? 'online' : '' }}"></span>
    @endif
</span>