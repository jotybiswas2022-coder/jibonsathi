@extends('frontend.layouts.member')

@section('title', 'Accepted Interests')

@section('member-content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Accepted Interests</h1>
            <p class="page-sub">It's a match — start a conversation!</p>
        </div>
    </div>

    @include('frontend.interests.partials.tabs', ['active' => 'accepted'])

    @if ($interests->isEmpty())
        <x-frontend::empty-state :icon="'fa-heart'" :title="'No accepted interests yet'"
            :description="'When someone accepts your interest, you will find them here and can start chatting.'" />
    @else
        <div class="list-stack">
            @foreach ($interests as $interest)
                @php($partner = $interest->otherParty(auth()->id()))
                <div class="card card-pad list-item">
                    <x-frontend::avatar :user="$partner" :size="64" :showOnline="true" />
                    <div style="flex:1;min-width:0">
                        <div class="flex items-center justify-between" style="gap:10px;flex-wrap:wrap">
                            <a href="{{ route('profiles.show', $partner) }}" class="list-name">
                                {{ $partner->name }}
                                @if ($partner->isVerifiedProfile()) <i class="fas fa-circle-check" style="color:#2563eb;font-size:13px"></i> @endif
                            </a>
                            @if (($scores[$partner->id]['percentage'] ?? null) !== null)
                                <span class="pcard-match" style="position:static">{{ $scores[$partner->id]['percentage'] }}% Match</span>
                            @endif
                        </div>
                        <div class="pcard-meta" style="gap:12px;margin-top:4px">
                            <span><i class="fas fa-cake-candles"></i> {{ $partner->age() ?? '—' }} yrs</span>
                            <span><i class="fas fa-location-dot"></i> {{ $partner->profile?->locationLabel() }}</span>
                        </div>
                        <div class="text-tiny text-muted" style="margin-top:4px">
                            Accepted {{ $interest->responded_at?->diffForHumans() }}
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:8px;min-width:150px">
                        <form method="POST" action="{{ route('messages.start', $partner) }}">
                            @csrf
                            <button class="btn btn-primary btn-block btn-sm"><i class="fas fa-comments"></i> Say Hello</button>
                        </form>
                        <a href="{{ route('profiles.show', $partner) }}" class="btn btn-soft btn-block btn-sm">View Profile</a>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $interests->links('frontend.components.pagination') }}</div>
    @endif
@endsection