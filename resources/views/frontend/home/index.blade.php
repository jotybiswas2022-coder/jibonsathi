@extends('frontend.layouts.app')

@section('title', 'Jibon Sathi — Find Someone Who Complements Your Life')

@section('content')
    @php($heroUsers = $featured->take(4)->values())
    @php($heroMain = $heroUsers->first())

    {{-- ============================= HERO ============================= --}}
    <section class="hero">
        <div class="hero-deco-blob b1"></div>
        <div class="hero-deco-blob b2"></div>

        <div class="container">
            <div class="hero-inner">
                <div class="hero-copy">
                    <span class="hero-pill"><i class="fas fa-heart"></i> 100% free — forever, for everyone</span>
                    <h1>Find Someone Who <span class="hl">Complements</span> Your Life</h1>
                    <p class="hero-sub">Meaningful connections, genuine verified profiles, and a better way to find your life partner — built on trust, privacy and family values.</p>

                    <div class="hero-ctas">
                        <a href="{{ route('discover.index') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-magnifying-glass"></i> Find Your Match
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-brand-outline btn-lg">
                            <i class="fas fa-user-plus"></i> Create Your Profile
                        </a>
                    </div>

                    <div class="hero-trust-row">
                        <span><i class="fas fa-circle-check"></i> Manually reviewed profiles</span>
                        <span><i class="fas fa-lock"></i> Private by default</span>
                        <span><i class="fas fa-bangladeshi-taka-sign"></i> No hidden charges</span>
                    </div>

                    <div class="hero-stat">
                        <div>
                            <div class="num" data-counter="{{ $stats['members'] }}" data-suffix="+">{{ number_format($stats['members']) }}+</div>
                            <div class="lbl">Members</div>
                        </div>
                        <div>
                            <div class="num" data-counter="{{ $stats['verified'] }}">{{ number_format($stats['verified']) }}</div>
                            <div class="lbl">Verified Profiles</div>
                        </div>
                        <div>
                            <div class="num" data-counter="{{ $stats['stories'] }}">{{ number_format($stats['stories']) }}</div>
                            <div class="lbl">Success Stories</div>
                        </div>
                        <div>
                            <div class="num" data-counter="{{ $stats['divisions'] }}">{{ $stats['divisions'] }}</div>
                            <div class="lbl">Locations</div>
                        </div>
                    </div>
                </div>

                <div class="hero-visual reveal">
                    <div class="hero-mandala"></div>

                    <div class="hero-arch">
                        <div class="hero-arch-photo">
                            @if ($heroMain)
                                <img src="{{ $heroMain->photoUrl() }}" alt="{{ $heroMain->name }}">
                            @else
                                <span>💍</span>
                            @endif
                        </div>
                        <div class="hero-arch-badge">
                            <i class="fas fa-hands-holding-heart"></i> Rishta ready
                        </div>
                    </div>

                    @if ($heroUsers->count() >= 2)
                        <div class="hero-orb orb-1">
                            <img src="{{ $heroUsers[1]->photoUrl() }}" alt="{{ $heroUsers[1]->name }}">
                        </div>
                    @endif
                    @if ($heroUsers->count() >= 3)
                        <div class="hero-orb orb-2">
                            <img src="{{ $heroUsers[2]->photoUrl() }}" alt="{{ $heroUsers[2]->name }}">
                        </div>
                    @endif

                    <div class="hero-float-chip hero-chip-1">
                        <span class="badge badge-success"><i class="fas fa-badge-check"></i> Verified</span>
                        Genuine profiles only
                    </div>
                    <div class="hero-float-chip hero-chip-3">
                        <span class="badge badge-brand"><i class="fas fa-heart"></i> 92% Match</span>
                        Great compatibility found
                    </div>

                    <span class="hero-petal p1"></span>
                    <span class="hero-petal p2"></span>
                    <span class="hero-petal p3"></span>
                    <span class="hero-petal p4"></span>
                    <span class="hero-petal p5"></span>
                    <span class="hero-petal p6"></span>
                </div>
            </div>
        </div>
    </section>

    {{-- =========================== SEARCH PANEL =========================== --}}
    <div class="container">
        <form action="{{ route('discover.index') }}" method="GET" class="search-panel">
            <div class="search-panel-title">
                <i class="fas fa-filter"></i>
                Search your life partner
            </div>
            <div class="search-grid">
                <div class="field">
                    <label><i class="fas fa-venus-mars"></i> Looking For</label>
                    <select name="gender" class="input">
                        <option value="">Bride or Groom</option>
                        <option value="male">Groom (Male)</option>
                        <option value="female">Bride (Female)</option>
                    </select>
                </div>
                <div class="field">
                    <label><i class="fas fa-cake-candles"></i> Age From</label>
                    <select name="age_from" class="input">
                        <option value="">Any</option>
                        @for ($i = 18; $i <= 50; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="field">
                    <label><i class="fas fa-cake-candles"></i> Age To</label>
                    <select name="age_to" class="input">
                        <option value="">Any</option>
                        @for ($i = 19; $i <= 60; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="field">
                    <label><i class="fas fa-location-dot"></i> Location</label>
                    <select name="division" class="input">
                        <option value="">Anywhere</option>
                        @foreach (\App\Support\Reference::divisionNames() as $division)
                            <option value="{{ $division }}">{{ $division }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label><i class="fas fa-mosque"></i> Religion</label>
                    <select name="religion" class="input">
                        <option value="">Any</option>
                        @foreach (\App\Support\Reference::religions() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label><i class="fas fa-ring"></i> Marital Status</label>
                    <select name="marital_status" class="input">
                        <option value="">Any</option>
                        @foreach (\App\Support\Reference::maritalStatuses() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="search-panel-actions">
                <div class="quick-chips">
                    <a href="{{ route('discover.index', ['gender' => 'female']) }}" class="quick-chip"><i class="fas fa-user"></i> Brides</a>
                    <a href="{{ route('discover.index', ['gender' => 'male']) }}" class="quick-chip"><i class="fas fa-user"></i> Grooms</a>
                    <a href="{{ route('discover.index', ['division' => 'Dhaka']) }}" class="quick-chip"><i class="fas fa-location-dot"></i> Dhaka</a>
                    <a href="{{ route('success-stories.index') }}" class="quick-chip"><i class="fas fa-heart"></i> Success Stories</a>
                </div>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-magnifying-glass"></i> Search Profiles
                </button>
            </div>
        </form>
    </div>

    {{-- ============================ BROWSE BY ============================ --}}
    <section class="section section-browse">
        <div class="container">
            <div class="section-head reveal">
                <span class="section-eyebrow"><i class="fas fa-compass"></i> Browse Matches</span>
                <h2>Start From What Matters Most</h2>
                <p>Jump straight into the matches that fit your family, faith and future plans.</p>
            </div>

            <div class="browse-grid">
                <div class="card browse-card reveal">
                    <div class="browse-head">
                        <span class="browse-ico"><i class="fas fa-mosque"></i></span>
                        <div>
                            <h3>By Religion</h3>
                            <p>Find a partner who shares your faith.</p>
                        </div>
                    </div>
                    <div class="browse-chips">
                        @foreach (\App\Support\Reference::religions() as $key => $label)
                            <a href="{{ route('discover.index', ['religion' => $key]) }}" class="browse-chip">
                                {{ $label }} <i class="fas fa-arrow-right"></i>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="card browse-card reveal" style="--reveal-delay:100ms">
                    <div class="browse-head">
                        <span class="browse-ico accent"><i class="fas fa-graduation-cap"></i></span>
                        <div>
                            <h3>By Education</h3>
                            <p>Meet members with a similar background.</p>
                        </div>
                    </div>
                    <div class="browse-chips">
                        @foreach (\App\Support\Reference::educationLevels() as $key => $label)
                            <a href="{{ route('discover.index', ['education' => $key]) }}" class="browse-chip">
                                {{ $label }} <i class="fas fa-arrow-right"></i>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="card browse-card reveal" style="--reveal-delay:200ms">
                    <div class="browse-head">
                        <span class="browse-ico info"><i class="fas fa-location-dot"></i></span>
                        <div>
                            <h3>By Location</h3>
                            <p>Search profiles across all eight divisions.</p>
                        </div>
                    </div>
                    <div class="browse-chips">
                        @foreach (\App\Support\Reference::divisionNames() as $division)
                            <a href="{{ route('discover.index', ['division' => $division]) }}" class="browse-chip">
                                {{ $division }} <i class="fas fa-arrow-right"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========================= FEATURED PROFILES ========================= --}}
    <section class="section" style="padding-top:0">
        <div class="container">
            <div class="section-head reveal">
                <span class="section-eyebrow"><i class="fas fa-star"></i> Featured Profiles</span>
                <h2>Meet Members Ready To Settle Down</h2>
                <p>Handpicked, genuine profiles from verified members who are serious about a meaningful relationship.</p>
            </div>
            <div class="featured-band">
                <div class="grid grid-profiles reveal">
                    @forelse ($featured as $member)
                        @include('frontend.components.profile-card', ['user' => $member, 'score' => $scores[$member->id] ?? null])
                    @empty
                        <x-frontend::empty-state
                            icon="fa-users"
                            title="Profiles are on the way"
                            message="Genuine profiles are being added. Please check back soon."
                        />
                    @endforelse
                </div>

                @php($spotlight = $stories->first())
                @if ($spotlight)
                    <div class="fb-side reveal" style="--reveal-delay:120ms">
                        <a href="{{ route('success-stories.show', $spotlight) }}" class="fb-story-card">
                            <span class="fb-eyebrow"><i class="fas fa-briefcase"></i> Success Story</span>
                            <blockquote>“{{ \Illuminate\Support\Str::limit($spotlight->story, 150) }}”</blockquote>
                            <div class="fb-couple">{{ $spotlight->groom_name }} &amp; {{ $spotlight->bride_name }}</div>
                            @if ($spotlight->location)
                                <div class="fb-loc"><i class="fas fa-location-dot"></i> {{ $spotlight->location }}</div>
                            @endif
                            <span class="fb-link">Read their story <i class="fas fa-arrow-right"></i></span>
                        </a>
                    </div>
                @endif
            </div>
            <div class="text-center mt-6 reveal">
                <a href="{{ route('discover.index') }}" class="btn btn-brand-outline btn-lg">
                    <i class="fas fa-arrow-right"></i> Explore All Profiles
                </a>
            </div>
        </div>
    </section>

    {{-- ========================== NEW MEMBERS (marquee) ========================== --}}
    @if ($newest->isNotEmpty())
        <section class="section-tight section-new">
            <div class="container">
                <div class="section-head reveal">
                    <span class="section-eyebrow"><i class="fas fa-user-plus"></i> Just Joined</span>
                    <h2>New Members This Season</h2>
                    <p>Fresh profiles added recently — be among the first to say hello.</p>
                </div>
            </div>

            <div class="marquee reveal">
                <div class="marquee-track">
                    @foreach ($newest as $member)
                        <a href="{{ route('profiles.show', $member) }}" class="member-pill">
                            <span class="avatar avatar-md"><img src="{{ $member->photoUrl() }}" alt="{{ $member->name }}" loading="lazy"></span>
                            <span class="mp-info">
                                <span class="mp-name">
                                    {{ $member->name }}
                                    @if ($member->isVerifiedProfile())<i class="fas fa-circle-check mp-verify"></i>@endif
                                </span>
                                <span class="mp-meta">
                                    @if ($member->age()){{ $member->age() }} yrs · @endif{{ $member->profile?->locationLabel() }}
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
                <div class="marquee-track" aria-hidden="true">
                    @foreach ($newest as $member)
                        <a href="{{ route('profiles.show', $member) }}" class="member-pill" tabindex="-1">
                            <span class="avatar avatar-md"><img src="{{ $member->photoUrl() }}" alt="" loading="lazy"></span>
                            <span class="mp-info">
                                <span class="mp-name">
                                    {{ $member->name }}
                                    @if ($member->isVerifiedProfile())<i class="fas fa-circle-check mp-verify"></i>@endif
                                </span>
                                <span class="mp-meta">
                                    @if ($member->age()){{ $member->age() }} yrs · @endif{{ $member->profile?->locationLabel() }}
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="text-center mt-5">
                <a href="{{ route('discover.index', ['sort' => 'newest']) }}" class="btn btn-soft">
                    <i class="fas fa-arrow-right"></i> See Newest Members
                </a>
            </div>
        </section>
    @endif

    {{-- ============================ HOW IT WORKS ============================ --}}
    <section class="section" style="background:linear-gradient(180deg,#FFF3EC,#FFF9F5)">
        <div class="container">
            <div class="section-head reveal">
                <span class="section-eyebrow"><i class="fas fa-compass"></i> How It Works</span>
                <h2>Four Simple Steps To Forever</h2>
                <p>Getting started with Jibon Sathi takes only a few minutes — the rest is up to chemistry.</p>
            </div>
            <div class="steps">
                <div class="card step-card reveal">
                    <span class="step-ico"><i class="fas fa-user-pen"></i></span>
                    <div class="step-num">01</div>
                    <h3>Create Your Profile</h3>
                    <p>Tell us about yourself, your family, career and what matters most to you — all free.</p>
                </div>
                <div class="card step-card reveal" style="--reveal-delay:100ms">
                    <span class="step-ico"><i class="fas fa-wand-magic-sparkles"></i></span>
                    <div class="step-num">02</div>
                    <h3>Discover Matches</h3>
                    <p>Our smart matching engine finds compatible profiles based on your preferences.</p>
                </div>
                <div class="card step-card reveal" style="--reveal-delay:200ms">
                    <span class="step-ico"><i class="fas fa-comments"></i></span>
                    <div class="step-num">03</div>
                    <h3>Connect</h3>
                    <p>Send an interest, get matched, and start chatting securely once you both connect.</p>
                </div>
                <div class="card step-card reveal" style="--reveal-delay:300ms">
                    <span class="step-ico"><i class="fas fa-heart"></i></span>
                    <div class="step-num">04</div>
                    <h3>Find Your Life Partner</h3>
                    <p>Take it from a conversation to a lifelong bond — and join our success stories.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- =========================== WHY CHOOSE JIBON SATHI =========================== --}}
    <section class="section">
        <div class="container">
            <div class="section-head reveal">
                <span class="section-eyebrow"><i class="fas fa-heart"></i> Why Choose Jibon Sathi</span>
                <h2>Built On Trust, Not Paywalls</h2>
                <p>Every feature on Jibon Sathi is completely free — forever. No hidden charges, no premium tiers.</p>
            </div>
            <div class="feature-grid">
                <div class="card feature-tile span-2 reveal">
                    <div class="ft-ico" style="background:linear-gradient(135deg,var(--brand),var(--brand-700));color:#fff"><i class="fas fa-wand-magic-sparkles"></i></div>
                    <h3>Smart Matching</h3>
                    <p>Age, lifestyle, education, family values and more — our matching engine weighs what truly matters and finds real compatibility, so you start from genuine common ground.</p>
                </div>
                <div class="card feature-tile reveal" style="--reveal-delay:100ms">
                    <div class="ft-ico" style="background:var(--success-bg);color:var(--success)"><i class="fas fa-badge-check"></i></div>
                    <h3>Verified Profiles</h3>
                    <p>Identity checks keep fake profiles away so you can trust who you meet.</p>
                </div>
                <div class="card feature-tile reveal" style="--reveal-delay:200ms">
                    <div class="ft-ico" style="background:var(--info-bg);color:var(--info)"><i class="fas fa-user-shield"></i></div>
                    <h3>Privacy First</h3>
                    <p>Control who sees your profile, your phone, and your email. Your information stays private.</p>
                </div>
                <div class="card feature-tile reveal">
                    <div class="ft-ico" style="background:var(--warning-bg);color:var(--warning)"><i class="fas fa-gift"></i></div>
                    <h3>Completely Free</h3>
                    <p>No payments, no subscriptions, no premium plans. A quality matrimony platform for everyone.</p>
                </div>
                <div class="card feature-tile reveal" style="--reveal-delay:100ms">
                    <div class="ft-ico" style="background:#F3E8FF;color:#9333EA"><i class="fas fa-hands-holding-heart"></i></div>
                    <h3>Serious Connections</h3>
                    <p>Members here are looking for marriage — meaningful relationships, not casual browsing.</p>
                </div>
                <div class="card feature-tile reveal" style="--reveal-delay:200ms">
                    <div class="ft-ico" style="background:linear-gradient(135deg,var(--accent),var(--accent-600));color:#fff"><i class="fas fa-lock"></i></div>
                    <h3>Secure Communication</h3>
                    <p>Message only after mutual interest, with blocking and reporting to keep every conversation safe.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ======================== RECOMMENDED MATCHES ======================== --}}
    @if ($recommended->isNotEmpty())
        <section class="section" style="padding-top:0">
            <div class="container">
                <div class="section-head reveal">
                    <span class="section-eyebrow"><i class="fas fa-heart-circle-check"></i> For You</span>
                    <h2>Recommended For You</h2>
                    <p>Profiles selected based on your preferences and life choices.</p>
                </div>
                <div class="grid grid-profiles reveal">
                    @foreach ($recommended as $member)
                        @include('frontend.components.profile-card', ['user' => $member, 'score' => $scores[$member->id] ?? null])
                    @endforeach
                </div>
                <div class="text-center mt-6 reveal">
                    <a href="{{ route('matches.index') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-heart"></i> See All My Matches
                    </a>
                </div>
            </div>
        </section>
    @endif

    {{-- =========================== SUCCESS STORIES =========================== --}}
    @if ($stories->isNotEmpty())
        <section class="section" style="background:#FFF1EA">
            <div class="container">
                <div class="section-head reveal">
                    <span class="section-eyebrow"><i class="fas fa-briefcase"></i> Success Stories</span>
                    <h2>Two Lives, One Beautiful Story</h2>
                    <p>Real couples who found each other on Jibon Sathi and started their forever.</p>
                </div>
                <div class="grid grid-3">
                    @foreach ($stories as $index => $story)
                        <a href="{{ route('success-stories.show', $story) }}" class="card story-card card-hover reveal {{ $index === 0 ? 'feature' : '' }}" style="--reveal-delay:{{ ($index % 3) * 90 }}ms">
                            <div class="story-media">
                                @if ($story->photo_path)
                                    <img src="{{ $story->photoUrl() }}" alt="{{ $story->title }}" loading="lazy">
                                @else
                                    <div class="pcard-phill-img" style="aspect-ratio:1.6/1">
                                        <span style="font-size:40px">{{ $story->initials() }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="story-body">
                                <div class="story-couples mb-3">
                                    <span class="avatar"><span class="initials">{{ $story->initials() }}</span></span>
                                </div>
                                <h3 style="font-size:17px">{{ $story->title }}</h3>
                                <p class="story-quote">{{ \Illuminate\Support\Str::limit($story->story, 130) }}</p>
                                <span class="text-small" style="color:var(--brand);font-weight:600">
                                    {{ $story->groom_name }} &amp; {{ $story->bride_name }}
                                    @if ($story->location) · {{ $story->location }} @endif
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="text-center mt-6 reveal">
                    <a href="{{ route('success-stories.index') }}" class="btn btn-brand-outline btn-lg">Read More Stories</a>
                </div>
            </div>
        </section>
    @endif

    {{-- ============================== STATS BAND ============================== --}}
    <section class="section-tight" style="padding-bottom:0">
        <div class="container">
            <div class="stats-band reveal">
                <div class="sb-cell">
                    <div class="sb-num" data-counter="{{ $stats['members'] }}" data-suffix="+">{{ number_format($stats['members']) }}+</div>
                    <div class="sb-lbl">Registered Members</div>
                </div>
                <div class="sb-cell">
                    <div class="sb-num" data-counter="{{ $stats['verified'] }}">{{ number_format($stats['verified']) }}</div>
                    <div class="sb-lbl">Verified Profiles</div>
                </div>
                <div class="sb-cell">
                    <div class="sb-num" data-counter="{{ $stats['stories'] }}">{{ number_format($stats['stories']) }}</div>
                    <div class="sb-lbl">Marriages Celebrated</div>
                </div>
                <div class="sb-cell">
                    <div class="sb-num" data-counter="{{ $stats['divisions'] }}">{{ $stats['divisions'] }}</div>
                    <div class="sb-lbl">Divisions Covered</div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================== FINAL CTA ============================== --}}
    <section class="section">
        <div class="container">
            <div class="cta-band reveal">
                <h2>Ready To Find Your Match?</h2>
                <p>Join thousands of genuine members. Creating your profile is free, quick and completely private.</p>
                <a href="{{ route('register') }}" class="btn btn-accent btn-lg">
                    <i class="fas fa-user-plus"></i> Create Your Free Profile
                </a>
            </div>
        </div>
    </section>
@endsection
