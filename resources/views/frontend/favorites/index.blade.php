@extends('frontend.layouts.member')

@section('title', 'My Shortlist')

@section('member-content')
    <div class="page-head">
        <div>
            <h1 class="page-title">My Shortlist</h1>
            <p class="page-sub">{{ $total }} profile{{ $total === 1 ? '' : 's' }} saved for later.</p>
        </div>
        <a href="{{ route('discover.index') }}" class="btn btn-primary"><i class="fas fa-search"></i> Find More</a>
    </div>

    @if ($favorites->isEmpty())
        <x-frontend::empty-state :icon="'fa-star'" :title="'Your shortlist is empty'"
            :description="'Save profiles you like by tapping the star on any profile card.'" />
    @else
        <div class="grid-3 cards-grid">
            @foreach ($favorites as $favorite)
                @php($candidate = $favorite->favoriteUser)
                <x-frontend::profile-card :user="$candidate" :score="$scores[$candidate->id] ?? null" />
            @endforeach
        </div>
        <div class="mt-4">{{ $favorites->links('frontend.components.pagination') }}</div>
    @endif
@endsection