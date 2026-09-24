@props(['user', 'score' => null, 'showActions' => true])

@php
    $photo = $user->photoUrl();
    $isAvatar = \Str::contains($photo, '/media/avatar/');
    $percentage = $score['percentage'] ?? null;
    $isShortlisted = auth()->check() && $user->isShortlistedBy(auth()->user());
    $isSelf = auth()->check() && auth()->user()->id === $user->id;
    $eduLabel = \App\Support\Reference::educationLevels()[$user->education?->level] ?? $user->education?->degree ?? 'Education not set';
@endphp

<article class="profile-card">
    <a href="{{ route('profiles.show', $user) }}" class="pcard-top">
        @if ($isAvatar)
            <div class="pcard-phill-img">
                <span>{{ $user->initials }}</span>
                <span class="text-nowrap" style="font-size:13px;font-weight:600;color:var(--muted)">{{ $user->name }}</span>
            </div>
        @else
            <img src="{{ $photo }}" alt="{{ $user->name }}" loading="lazy">
        @endif

        @if ($percentage !== null)
            <span class="pcard-match">{{ $percentage }}% Match</span>
        @endif

        @if ($user->isVerifiedProfile())
            <span class="pcard-verify"><span class="badge badge-info"><i class="fas fa-circle-check"></i> Verified</span></span>
        @endif
    </a>

    <div class="pcard-body">
        <div class="pcard-name">
            <a href="{{ route('profiles.show', $user) }}">{{ $user->name }}</a>
            @if ($user->isOnline() && ($user->profile?->show_online_status ?? true))
                <span class="badge-dot online" title="Online"></span>
            @endif
        </div>
        <div class="pcard-meta">
            <span><i class="fas fa-location-dot"></i> {{ $user->profile?->locationLabel() }}</span>
            @if ($user->age())
                <span><i class="fas fa-cake-candles"></i> {{ $user->age() }} yrs
                    @if ($user->profile?->height_cm) · {{ $user->profile->heightLabel() }} @endif
                </span>
            @endif
            <span><i class="fas fa-graduation-cap"></i> {{ $eduLabel }}</span>
            @if ($user->occupation?->designation)
                <span><i class="fas fa-briefcase"></i> {{ $user->occupation->designation }}</span>
            @endif
        </div>

        @if ($showActions && !$isSelf)
            <div class="pcard-actions">
                <a href="{{ route('profiles.show', $user) }}" class="btn btn-soft btn-sm">
                    <i class="fas fa-eye"></i> View Profile
                </a>
                @auth
                    @if ($user->canInteractWith(auth()->user()))
                        <form method="POST" action="{{ $isShortlisted ? route('favorites.destroy', $user) : route('favorites.store', $user) }}">
                            @csrf
                            @if ($isShortlisted) @method('DELETE') @endif
                            <button type="submit"
                                    class="btn-icon btn-icon-sm {{ $isShortlisted ? 'active' : '' }}"
                                    title="{{ $isShortlisted ? 'Remove from shortlist' : 'Shortlist' }}">
                                <i class="{{ $isShortlisted ? 'fas' : 'far' }} fa-star"></i>
                            </button>
                        </form>
                    @endif
                @endauth
            </div>
        @endif
    </div>
</article>