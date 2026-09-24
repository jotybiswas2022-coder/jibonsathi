@extends('frontend.layouts.app')

@section('title', 'About Jibon Sathi')

@section('content')
<div class="container table-page" style="max-width:880px;padding-block:48px">
    <div class="page-head hero-head" style="text-align:center;flex-direction:column;align-items:center">
        <h1 class="page-title" style="margin:0">About Jibon Sathi</h1>
        <p class="page-sub">Bringing two lives together.</p>
    </div>

    <div class="card card-pad" style="padding:clamp(24px,4vw,40px)">
        <p style="line-height:1.9;color:var(--muted)">
            <strong style="color:var(--text)">Jibon Sathi</strong> (জীবনসাথী — meaning "life partner") is a modern, free matrimony platform.
            We believe finding a life partner should be meaningful, respectful and — most importantly — free for everyone
            who is serious about taking this step.
        </p>
        <p style="line-height:1.9;color:var(--muted)">
            Every profile on Jibon Sathi is manually reviewed by our moderation team before it appears in search results.
            Your privacy is protected with careful defaults, and your identity documents are stored on private servers and
            only used to verify you're really you.
        </p>
        <p style="line-height:1.9;color:var(--muted)">
            Our compatibility engine looks beyond looks — it weighs religion, education, career and lifestyle so you can
            start from a place of genuine common ground.
        </p>
    </div>

    <div class="grid-3" style="margin-top:24px">
        @foreach ([
            ['icon' => 'fa-shield-halved', 'num' => '100%', 'label' => 'Profiles reviewed by people'],
            ['icon' => 'fa-gift', 'num' => 'Free', 'label' => 'No paid plans. Ever.'],
            ['icon' => 'fa-lock', 'num' => 'Private', 'label' => 'Identity checks stay private'],
        ] as $item)
            <div class="card card-pad value-card" style="text-align:center">
                <div class="es-ico" style="margin:0 auto 14px"><i class="fas {{ $item['icon'] }}"></i></div>
                <div style="font-weight:800;font-family:var(--font-display);font-size:22px;color:var(--brand)">{{ $item['num'] }}</div>
                <div class="text-muted text-small">{{ $item['label'] }}</div>
            </div>
        @endforeach
    </div>

    <div style="text-align:center;margin-top:34px">
        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Join Jibon Sathi Free</a>
    </div>
</div>
@endsection