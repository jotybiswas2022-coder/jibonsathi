@extends('frontend.layouts.app')

@section('title', $story->title)

@section('content')
<div class="container story-single" style="max-width:820px;padding-block:48px">
    <nav class="crumbs" aria-label="Breadcrumb">
        <a href="{{ route('success-stories.index') }}"><i class="fas fa-arrow-left"></i> All Success Stories</a>
    </nav>

    <article class="card card-pad" style="padding:clamp(24px,4vw,44px)">
        <div class="story-media story-media-lg">
            @if ($story->photoUrl())
                <img src="{{ $story->photoUrl() }}" alt="{{ $story->coupleLabel() }}">
            @else
                <div class="story-initials"><span>{{ $story->initials() }}</span></div>
            @endif
        </div>

        <h1 class="story-title-lg" style="margin:24px 0 4px">{{ $story->title }}</h1>
        <div class="story-couple" style="font-weight:700;color:var(--brand);font-size:17px">{{ $story->coupleLabel() }}</div>
        <div class="story-meta" style="gap:16px;margin-top:6px">
            <span><i class="fas fa-location-dot"></i> {{ $story->location }}</span>
            @if ($story->married_on)
                <span><i class="fas fa-calendar-days"></i> Married {{ $story->married_on->format('F Y') }}</span>
            @endif
        </div>

        <div class="divider"></div>
        <p style="line-height:1.9;color:var(--muted)">{!! nl2br(e($story->story)) !!}</p>
    </article>

    @if ($related->isNotEmpty())
        <div style="margin-top:34px">
            <h3 class="card-title"><i class="fas fa-heart"></i> More Love Stories</h3>
            <div class="story-grid">
                @foreach ($related as $item)
                    <a href="{{ route('success-stories.show', $item) }}" class="story-card">
                        <div class="story-media">
                            @if ($item->photoUrl())
                                <img src="{{ $item->photoUrl() }}" alt="{{ $item->coupleLabel() }}" loading="lazy">
                            @else
                                <div class="story-initials"><span>{{ $item->initials() }}</span></div>
                            @endif
                        </div>
                        <div class="story-body">
                            <div class="story-title">{{ $item->title }}</div>
                            <div class="story-couple">{{ $item->coupleLabel() }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection