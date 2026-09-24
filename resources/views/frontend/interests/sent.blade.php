@extends('frontend.layouts.member')

@section('title', 'Interests Sent')

@section('member-content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Interests You've Sent</h1>
            <p class="page-sub">Follow up with people you reached out to.</p>
        </div>
    </div>

    @include('frontend.interests.partials.tabs', ['active' => 'sent'])

    @if ($interests->isEmpty())
        <x-frontend::empty-state :icon="'fa-paper-plane'" :title="'No interests sent yet'"
            :description="'Browse the discover page and send an interest to someone you like.'" />
    @else
        <div class="list-stack">
            @foreach ($interests as $interest)
                @php($receiver = $interest->receiver)
                <div class="card card-pad list-item">
                    <x-frontend::avatar :user="$receiver" :size="64" :showOnline="true" />
                    <div style="flex:1;min-width:0">
                        <div class="flex items-center justify-between" style="gap:10px;flex-wrap:wrap">
                            <a href="{{ route('profiles.show', $receiver) }}" class="list-name">
                                {{ $receiver->name }}
                                @if ($receiver->isVerifiedProfile()) <i class="fas fa-circle-check" style="color:#2563eb;font-size:13px"></i> @endif
                            </a>
                            @if (($scores[$receiver->id]['percentage'] ?? null) !== null)
                                <span class="pcard-match" style="position:static">{{ $scores[$receiver->id]['percentage'] }}% Match</span>
                            @endif
                        </div>
                        <div class="pcard-meta" style="gap:12px;margin-top:4px">
                            <span><i class="fas fa-cake-candles"></i> {{ $receiver->age() ?? '—' }} yrs</span>
                            <span><i class="fas fa-location-dot"></i> {{ $receiver->profile?->locationLabel() }}</span>
                        </div>
                        <div class="text-tiny text-muted" style="margin-top:4px">
                            Sent {{ $interest->created_at->diffForHumans() }}
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:8px;min-width:150px">
                        @if ($interest->isPending())
                            <x-frontend::badge :tone="'warning'">Awaiting response</x-frontend::badge>
                            <form method="POST" action="{{ route('interests.cancel', $interest) }}">
                                @csrf
                                <button class="btn btn-ghost btn-block btn-sm text-muted" data-confirm="Withdraw this interest?">Withdraw</button>
                            </form>
                        @else
                            <x-frontend::badge :tone="$interest->statusTone()">{{ $interest->statusLabel() }}</x-frontend::badge>
                        @endif
                        <a href="{{ route('profiles.show', $receiver) }}" class="btn btn-soft btn-block btn-sm">View Profile</a>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $interests->links('frontend.components.pagination') }}</div>
    @endif
@endsection