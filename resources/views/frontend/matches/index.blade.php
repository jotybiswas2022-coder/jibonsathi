@extends('frontend.layouts.member')

@section('title', 'My Matches')

@section('member-content')
    <div class="page-head">
        <div>
            <h1 class="page-title">My Matches</h1>
            <p class="page-sub">Profiles ranked by how well they fit your preferences.</p>
        </div>
        <form method="POST" action="{{ route('matches.refresh') }}">
            @csrf
            <button class="btn btn-soft"><i class="fas fa-sync-alt"></i> Refresh Scores</button>
        </form>
    </div>

    <div class="tabs-card" data-tabs style="margin-bottom:20px">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('matches.index', ['tab' => $key]) }}"
               data-tab="{{ $key }}"
               class="tab-item {{ $tab === $key ? 'active' : '' }}">
                {{ $label }}
                @if (($counts[$key] ?? 0) > 0) <span class="badge-count">{{ $counts[$key] }}</span> @endif
            </a>
        @endforeach
    </div>

    @if (! empty($highlights))
        <div class="flex gap-3" style="gap:12px;margin-bottom:22px;flex-wrap:wrap">
            @foreach ($highlights as $match)
                <a href="{{ route('profiles.show', $match) }}" class="hl-pill">
                    <x-frontend::avatar :user="$match" :size="34" />
                    <div>
                        <div class="hl-name">{{ $match->name }}</div>
                        <div class="text-tiny" style="color:#8a5a1a;font-weight:700">{{ $scores[$match->id]['percentage'] ?? '' }}% match</div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    @if ($matches->isEmpty())
        <x-frontend::empty-state :icon="'fa-heart-crack'" :title="'No matches to show'"
            :description="'Refresh your scores or broaden your partner preferences to find matches.'" />
    @else
        <div class="grid-3 cards-grid">
            @foreach ($matches as $matchRecord)
                @php($candidate = $matchRecord->matchedUser)
                <x-frontend::profile-card :user="$candidate" :score="$scores[$candidate->id] ?? null" />
            @endforeach
        </div>
        <div class="mt-4">{{ $matches->links('frontend.components.pagination') }}</div>
    @endif
@endsection