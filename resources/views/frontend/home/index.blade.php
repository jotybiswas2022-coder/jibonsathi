@extends('frontend.layouts.app')

@section('title', 'Jora — Find Someone Who Complements Your Life')

@section('content')
    {{-- ============================= HERO ============================= --}}
    <section class="hero">
        <div class="container">
            <div class="hero-inner">
                <div>
                    <span class="hero-pill"><i class="fas fa-heart"></i> Completely free for everyone</span>
                    <h1>Find Someone Who <span class="hl">Complements</span> Your Life</h1>
                    <p class="hero-sub">Meaningful connections, genuine profiles, and a better way to find your life partner.</p>
                    <div class="hero-ctas">
                        <a href="{{ route('discover.index') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-magnifying-glass"></i> Find Your Match
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-brand-outline btn-lg">
                            <i class="fas fa-user-plus"></i> Create Your Profile
                        </a>
                    </div>
                    <div class="hero-stat">
                        <div><div class="num">{{ number_format($stats['members']) }}+</div><div class="lbl">Members</div></div>
                        <div><div class="num">{{ number_format($stats['verified']) }}</div><div class="lbl">Verified Profiles</div></div>
                        <div><div class="num">{{ number_format($stats['stories']) }}</div><div class="lbl">Success Stories</div></div>
                        <div><div class="num">{{ $stats['divisions'] }}</div><div class="lbl">Locations</div></div>
                    </div>
                </div>

                <div class="hero-visual">
                    <div class="hero-photo-ring">
                        @php($heroUsers = $featured->take(3)->values())
                        @if ($heroUsers->count() >= 3)
                            <div class="hero-photo-card hero-photo-1">
                                <img src="{{ $heroUsers[0]->photoUrl() }}" alt="{{ $heroUsers[0]->name }}">
                            </div>
                            <div class="hero-photo-card hero-photo-2">
                                <img src="{{ $heroUsers[1]->photoUrl() }}" alt="{{ $heroUsers[1]->name }}">
                            </div>
                            <div class="hero-photo-card hero-photo-3">
                                <img src="{{ $heroUsers[2]->photoUrl() }}" alt="{{ $heroUsers[2]->name }}">
                            </div>
                        @else
                            <div class="hero-photo-card hero-photo-1">
                                <div class="pcard-phill-img"><span>👰</span></div>
                            </div>
                            <div class="hero-photo-card hero-photo-2">
                                <div class="pcard-phill-img"><span>🤵</span></div>
                            </div>
                            <div class="hero-photo-card hero-photo-3">
                                <div class="pcard-phill-img"><span>💍</span></div>
                            </div>
                        @endif
                    </div>
                    @php($first = $heroUsers->first())
                    <div class="hero-float-chip hero-chip-1">
                        <span class="avatar avatar-sm"><img src="{{ $first?->photoUrl() }}" alt=""></span>
                        <span>{{ $first?->name ?? 'Nusrat' }} &amp; joined Jora</span>
                    </div>
                    <div class="hero-float-chip hero-chip-2">
                        <span class="badge badge-success"><i class="fas fa-badge-check"></i> Verified</span>
                        Genuine profiles only
                    </div>
                    <div class="hero-float-chip hero-chip-3">
                        <span class="badge badge-brand"><i class="fas fa-heart"></i> 92% Match</span>
                        Great compatibility found
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- =========================== SEARCH PANEL =========================== --}}
    <div class="container">
        <form action="{{ route('discover.index') }}" method="GET" class="search-panel">
            <div class="search-panel-title">
                <i class="fas fa-filter" style="color:var(--brand)"></i>
                Search your life partner
            </div>
            <div class="search-grid">
                <div class="field">
                    <label>Looking For</label>
                    <select name="gender" class="input">
                        <option value="">Bride or Groom</option>
                        <option value="male">Groom (Male)</option>
                        <option value="female">Bride (Female)</option>
                    </select>
                </div>
                <div class="field">
                    <label>Age From</label>
                    <select name="age_from" class="input">
                        <option value="">Any</option>
                        @for ($i = 18; $i <= 50; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="field">
                    <label>Age To</label>
                    <select name="age_to" class="input">
                        <option value="">Any</option>
                        @for ($i = 19; $i <= 60; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="field">
                    <label>Location</label>
                    <select name="division" class="input">
                        <option value="">Anywhere</option>
                        @foreach (\App\Support\Reference::divisionNames() as $division)
                            <option value="{{ $division }}">{{ $division }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Religion</label>
                    <select name="religion" class="input">
                        <option value="">Any</option>
                        @foreach (\App\Support\Reference::religions() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Marital Status</label>
                    <select name="marital_status" class="input">
                        <option value="">Any</option>
                        @foreach (\App\Support\Reference::maritalStatuses() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex justify-between items-center mt-4 gap-3" style="flex-wrap:wrap">
                <a href="{{ route('discover.index') }}" class="text-muted text-small">Browse all profiles <i class="fas fa-arrow-right"></i></a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-magnifying-glass"></i> Search Profiles
                </button>
            </div>
        </form>
    </div>

    {{-- ========================= FEATURED PROFILES ========================= --}}
    <section class="section">
        <div class="container">
            <div class="section-head">
                <span class="section-eyebrow"><i class="fas fa-star"></i> Featured Profiles</span>
                <h2>Meet Members Ready To Settle Down</h2>
                <p>Handpicked, genuine profiles from verified members who are serious about a meaningful relationship.</p>
            </div>
            <div class="grid grid-4" style="grid-template-columns:repeat(auto-fill,minmax(250px,1fr))">
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
            <div class="text-center mt-6">
                <a href="{{ route('discover.index') }}" class="btn btn-brand-outline btn-lg">
                    <i class="fas fa-arrow-right"></i> Explore All Profiles
                </a>
            </div>
        </div>
    </section>

    {{-- ============================ HOW IT WORKS ============================ --}}
    <section class="section" style="background:linear-gradient(180deg,#FFF3EC,#FFF9F5)">
        <div class="container">
            <div class="section-head">
                <span class="section-eyebrow"><i class="fas fa-compass"></i> How It Works</span>
                <h2>Four Simple Steps To Forever</h2>
                <p>Getting started with Jora takes only a few minutes — the rest is up to chemistry.</p>
            </div>
            <div class="steps">
                <div class="card step-card">
                    <div class="step-num">01</div>
                    <h3>Create Your Profile</h3>
                    <p>Tell us about yourself, your family, career and what matters most to you — all free.</p>
                </div>
                <div class="card step-card">
                    <div class="step-num">02</div>
                    <h3>Discover Matches</h3>
                    <p>Our smart matching engine finds compatible profiles based on your preferences.</p>
                </div>
                <div class="card step-card">
                    <div class="step-num">03</div>
                    <h3>Connect</h3>
                    <p>Send an interest, get matched, and start chatting securely once you both connect.</p>
                </div>
                <div class="card step-card">
                    <div class="step-num">04</div>
                    <h3>Find Your Life Partner</h3>
                    <p>Take it from a conversation to a lifelong bond — and join our success stories.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- =========================== WHY CHOOSE JORA =========================== --}}
    <section class="section">
        <div class="container">
            <div class="section-head">
                <span class="section-eyebrow"><i class="fas fa-heart"></i> Why Choose Jora</span>
                <h2>Built On Trust, Not Paywalls</h2>
                <p>Every feature on Jora is completely free — forever. No hidden charges, no premium tiers.</p>
            </div>
            <div class="feature-grid">
                <div class="card feature-tile card-hover">
                    <div class="ft-ico" style="background:var(--success-bg);color:var(--success)"><i class="fas fa-badge-check"></i></div>
                    <h3>Verified Profiles</h3>
                    <p>Identity checks and a verification step keep fake profiles away so you can trust who you meet.</p>
                </div>
                <div class="card feature-tile card-hover">
                    <div class="ft-ico" style="background:var(--info-bg);color:var(--info)"><i class="fas fa-user-shield"></i></div>
                    <h3>Privacy First</h3>
                    <p>Control who sees your profile, your phone, and your email. Your information stays private.</p>
                </div>
                <div class="card feature-tile card-hover">
                    <div class="ft-ico" style="background:var(--brand-100);color:var(--brand)"><i class="fas fa-wand-magic-sparkles"></i></div>
                    <h3>Smart Matching</h3>
                    <p>Age, lifestyle, education, family values and more — our matching engine finds real compatibility.</p>
                </div>
                <div class="card feature-tile card-hover">
                    <div class="ft-ico" style="background:var(--accent);background:linear-gradient(135deg,var(--accent),var(--accent-600));color:#fff"><i class="fas fa-lock"></i></div>
                    <h3>Secure Communication</h3>
                    <p>Message only after mutual interest, with blocking and reporting to keep every conversation safe.</p>
                </div>
                <div class="card feature-tile card-hover">
                    <div class="ft-ico" style="background:var(--warning-bg);color:var(--warning)"><i class="fas fa-gift"></i></div>
                    <h3>Completely Free</h3>
                    <p>No payments, no subscriptions, no premium plans. A quality matrimony platform for everyone.</p>
                </div>
                <div class="card feature-tile card-hover">
                    <div class="ft-ico" style="background:#F3E8FF;color:#9333EA"><i class="fas fa-hands-holding-heart"></i></div>
                    <h3>Serious Connections</h3>
                    <p>Members here are looking for marriage — meaningful relationships, not casual browsing.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ======================== RECOMMENDED MATCHES ======================== --}}
    @if ($recommended->isNotEmpty())
        <section class="section" style="padding-top:0">
            <div class="container">
                <div class="section-head">
                    <span class="section-eyebrow"><i class="fas fa-heart-circle-check"></i> For You</span>
                    <h2>Recommended For You</h2>
                    <p>Profiles selected based on your preferences and life choices.</p>
                </div>
                <div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(250px,1fr))">
                    @foreach ($recommended as $member)
                        @include('frontend.components.profile-card', ['user' => $member, 'score' => $scores[$member->id] ?? null])
                    @endforeach
                </div>
                <div class="text-center mt-6">
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
                <div class="section-head">
                    <span class="section-eyebrow"><i class="fas fa-briefcase"></i> Success Stories</span>
                    <h2>Two Lives, One Beautiful Story</h2>
                    <p>Real couples who found each other on Jora and started their forever.</p>
                </div>
                <div class="grid grid-3">
                    @foreach ($stories as $story)
                        <a href="{{ route('success-stories.show', $story) }}" class="card story-card card-hover">
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
                                <p class="text-muted text-small">{{ \Illuminate\Support\Str::limit($story->story, 120) }}</p>
                                <span class="text-small" style="color:var(--brand);font-weight:600">
                                    {{ $story->groom_name }} &amp; {{ $story->bride_name }}
                                    @if ($story->location) · {{ $story->location }} @endif
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="text-center mt-6">
                    <a href="{{ route('success-stories.index') }}" class="btn btn-brand-outline btn-lg">Read More Stories</a>
                </div>
            </div>
        </section>
    @endif

    {{-- ============================== FINAL CTA ============================== --}}
    <section class="section">
        <div class="container">
            <div class="cta-band">
                <h2>Ready To Find Your Match?</h2>
                <p>Join thousands of genuine members. Creating your profile is free, quick and completely private.</p>
                <a href="{{ route('register') }}" class="btn btn-accent btn-lg">
                    <i class="fas fa-user-plus"></i> Create Your Free Profile
                </a>
            </div>
        </div>
    </section>
@endsection