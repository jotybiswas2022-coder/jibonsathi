@extends('frontend.layouts.member')

@section('title', 'Received Interests')

@section('member-content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Interest Requests</h1>
            <p class="page-sub">People who have expressed interest in your profile.</p>
        </div>
    </div>

    @include('frontend.interests.partials.tabs', ['active' => 'received'])

    @if ($interests->isEmpty())
        <x-frontend::empty-state :icon="'fa-inbox'" :title="'No interests received yet'"
            :description="'Complete your profile and stay active to attract requests.'" />
    @else
        <div class="list-stack">
            @foreach ($interests as $interest)
                @php($sender = $interest->sender)
                <div class="card card-pad list-item">
                    <x-frontend::avatar :user="$sender" :size="64" :showOnline="true" />
                    <div style="flex:1;min-width:0">
                        <div class="flex items-center justify-between" style="gap:10px;flex-wrap:wrap">
                            <a href="{{ route('profiles.show', $sender) }}" class="list-name">
                                {{ $sender->name }}
                                @if ($sender->isVerifiedProfile()) <i class="fas fa-circle-check" style="color:#2563eb;font-size:13px"></i> @endif
                            </a>
                            @if (($scores[$sender->id]['percentage'] ?? null) !== null)
                                <span class="pcard-match" style="position:static">{{ $scores[$sender->id]['percentage'] }}% Match</span>
                            @endif
                        </div>
                        <div class="pcard-meta" style="gap:12px;margin-top:4px">
                            <span><i class="fas fa-cake-candles"></i> {{ $sender->age() ?? '—' }} yrs</span>
                            <span><i class="fas fa-location-dot"></i> {{ $sender->profile?->locationLabel() }}</span>
                            @if ($sender->occupation?->designation)
                                <span><i class="fas fa-briefcase"></i> {{ $sender->occupation->designation }}</span>
                            @endif
                        </div>
                        <div class="text-tiny text-muted" style="margin-top:4px">
                            Sent {{ $interest->created_at->diffForHumans() }}
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:8px;min-width:150px">
                        @if ($interest->isPending())
                            <form method="POST" action="{{ route('interests.accept', $interest) }}">
                                @csrf
                                <button class="btn btn-primary btn-block btn-sm"><i class="fas fa-check"></i> Accept</button>
                            </form>
                            <form method="POST" action="{{ route('interests.reject', $interest) }}">
                                @csrf
                                <button class="btn btn-ghost btn-block btn-sm text-muted">Decline</button>
                            </form>
                        @else
                            <x-frontend::badge :tone="$interest->statusTone()">{{ $interest->statusLabel() }}</x-frontend::badge>
                            <a href="{{ route('profiles.show', $sender) }}" class="btn btn-soft btn-block btn-sm">View Profile</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $interests->links('frontend.components.pagination') }}</div>
    @endif
@endsection