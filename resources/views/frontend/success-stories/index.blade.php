@extends('frontend.layouts.app')

@section('title', 'Success Stories')

@section('content')
<div class="container table-page" style="max-width:1060px;padding-block:48px">
    <div class="page-head hero-head" style="text-align:center;flex-direction:column;align-items:center">
        <h1 class="page-title" style="margin:0">Real Couples. Real Love.</h1>
        <p class="page-sub">Stories of Jibon Sathi members who found their life partner through our community.</p>
    </div>

    @if ($stories->isEmpty())
        <x-frontend::empty-state :icon="'fa-heart'" :title="'Stories coming soon'"
            :description="'We are collecting beautiful love stories from our members. Check back soon!'" />
    @else
        <div class="story-grid">
            @foreach ($stories as $story)
                <a href="{{ route('success-stories.show', $story) }}" class="story-card">
                    <div class="story-media">
                        @if ($story->photoUrl())
                            <img src="{{ $story->photoUrl() }}" alt="{{ $story->coupleLabel() }}" loading="lazy">
                        @else
                            <div class="story-initials"><span>{{ $story->initials() }}</span></div>
                        @endif
                        @if ($story->is_featured)
                            <span class="story-featured"><i class="fas fa-crown"></i> Featured</span>
                        @endif
                    </div>
                    <div class="story-body">
                        <div class="story-title">{{ $story->title }}</div>
                        <div class="story-couple">{{ $story->coupleLabel() }}</div>
                        <div class="story-meta">
                            <span><i class="fas fa-location-dot"></i> {{ $story->location }}</span>
                            @if ($story->married_on)
                                <span><i class="fas fa-calendar-heart"></i> {{ $story->married_on->format('F Y') }}</span>
                            @endif
                        </div>
                        <p class="text-muted text-small story-excerpt">{{ \Illuminate\Support\Str::limit($story->story, 130) }}</p>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-5">{{ $stories->links('frontend.components.pagination') }}</div>
    @endif
</div>
@endsection