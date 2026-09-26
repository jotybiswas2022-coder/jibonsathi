@extends('frontend.layouts.app')

@section('title', 'How It Works')

@section('content')
<div class="page-hero">
    <div class="container">
        <span class="section-eyebrow"><i class="fas fa-compass"></i> How It Works</span>
        <h1>How Jibon Sathi Works</h1>
        <p>Four warm steps to the beginning of a beautiful journey.</p>
        <div class="page-hero-actions">
            <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-user-plus"></i> Create Free Profile
            </a>
            <a href="{{ route('discover.index') }}" class="btn btn-brand-outline btn-lg">
                <i class="fas fa-magnifying-glass"></i> Browse Members
            </a>
        </div>
    </div>
</div>

<div class="container table-page" style="max-width:880px;padding-block:40px">

    <div class="how-steps">
        @foreach ([
            ['icon' => 'fa-user-plus', 'title' => 'Create a free profile', 'text' => 'Sign up with your email and phone. It takes about two minutes and never costs a taka.'],
            ['icon' => 'fa-list-check', 'title' => 'Tell us what matters', 'text' => 'Share your education, career, family, lifestyle and partner preferences in a simple six-step wizard.'],
            ['icon' => 'fa-heart', 'title' => 'Find & shortlist', 'text' => 'Browse verified, vetted profiles and use our compatibility score to see who fits best.'],
            ['icon' => 'fa-comments', 'title' => 'Connect & chat', 'text' => 'When an interest is accepted, both sides agree — and you can start chatting right away.'],
        ] as $i => $step)
            <div class="how-step">
                <span class="how-num">{{ $i + 1 }}</span>
                <span class="how-icon"><i class="fas {{ $step['icon'] }}"></i></span>
                <h3>{{ $step['title'] }}</h3>
                <p class="text-muted">{{ $step['text'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="banner banner-gold mt-5" style="margin-top:34px">
        <div style="flex:1">
            <div style="font-weight:700;margin-bottom:4px">Ready to meet someone special?</div>
            <div class="text-muted text-small">Join thousands of families who found their match on Jibon Sathi.</div>
        </div>
        <a href="{{ route('register') }}" class="btn btn-primary">Create Free Profile</a>
    </div>
</div>
@endsection