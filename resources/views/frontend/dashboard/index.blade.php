@extends('frontend.layouts.member')

@section('title', 'Dashboard')

@section('member-content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Welcome, {{ Str::before(auth()->user()->name, ' ') }}</h1>
            <p class="page-sub">Here's what's happening on Jora today.</p>
        </div>
        <a href="{{ route('discover.index') }}" class="btn btn-primary">
            <i class="fas fa-search"></i> Find Matches
        </a>
    </div>

    {{-- Completion banner --}}
    <div class="banner banner-gold @if($completion === 100) banner-success-conf @endif" style="margin-bottom:24px">
        <div class="flex gap-4 items-center" style="flex-wrap:wrap;gap:18px">
            <span style="font-weight:800;font-family:var(--font-display);font-size:26px;color:var(--brand);min-width:60px">{{ $completion }}%</span>
            <div style="flex:1;min-width:200px">
                <div style="font-weight:700;margin-bottom:4px">
                    @if ($completion === 100)
                        Profile complete — great job!
                    @elseif ($completion >= 60)
                        Almost there — a little more polish and you're done.
                    @else
                        Complete your profile to appear in more searches.
                    @endif
                </div>
                <div class="progress" style="max-width:420px">
                    <div class="progress-fill" style="width:{{ $completion }}%"></div>
                </div>
            </div>
            <a href="{{ route('settings.profile') }}" class="btn btn-sm btn-ghost text-muted">Edit Profile</a>
        </div>
    </div>

    @if ($profile_status !== 'approved')
        <x-frontend::alert :tone="'warning'">
            Your profile is <strong>{{ ucfirst($profile_status) }}</strong>. It will become visible to other members once an admin approves it.
        </x-frontend::alert>
    @endif

    {{-- Stat cards --}}
    <div class="stat-grid">
        <a href="{{ route('matches.index') }}" class="stat-card">
            <span class="stat-ico" style="background:var(--brand-050);color:var(--brand)"><i class="fas fa-heart"></i></span>
            <span class="stat-num">{{ $stats['total_matches'] }}</span>
            <span class="stat-label">Total Matches</span>
        </a>
        <a href="{{ route('interests.received') }}" class="stat-card">
            <span class="stat-ico" style="background:#eef6ff;color:#2563eb"><i class="fas fa-envelope-open-text"></i></span>
            <span class="stat-num">{{ $stats['received_interests'] }}</span>
            <span class="stat-label">Interests Received</span>
        </a>
        <a href="{{ route('messages.index') }}" class="stat-card">
            <span class="stat-ico" style="background:#eefaf2;color:#16a34a"><i class="fas fa-comments"></i></span>
            <span class="stat-num">{{ $stats['unread_messages'] }}</span>
            <span class="stat-label">Unread Messages</span>
        </a>
        <a href="{{ route('favorites.index') }}" class="stat-card">
            <span class="stat-ico" style="background:#fdf2f8;color:#db2777"><i class="fas fa-star"></i></span>
            <span class="stat-num">{{ $stats['shortlisted'] }}</span>
            <span class="stat-label">Shortlisted</span>
        </a>
        <a href="{{ route('verification.index') }}" class="stat-card">
            <span class="stat-ico" style="background:#f5f3ff;color:#7c3aed"><i class="fas fa-shield-alt"></i></span>
            <span class="stat-num">{{ ucfirst($verification_status) }}</span>
            <span class="stat-label">Verification</span>
        </a>
        <a href="{{ route('messages.index') }}" class="stat-card">
            <span class="stat-ico" style="background:#fefce8;color:#ca8a04"><i class="fas fa-user-friends"></i></span>
            <span class="stat-num">{{ $stats['active_conversations'] }}</span>
            <span class="stat-label">Conversations</span>
        </a>
    </div>

    {{-- Completion checklist + recommended --}}
    <div class="member-cols">
        <div class="member-col">
            <div class="card card-pad" style="margin-bottom:24px">
                <h3 class="card-title"><i class="fas fa-list-check"></i> Profile Checklist</h3>
                <div class="checklist">
                    @forelse ($missing as $section)
                        <a href="{{ $section['url'] }}" class="check-row @if($section['done']) done @endif">
                            <span class="check-box">
                                @if($section['done']) <i class="fas fa-check"></i> @else <i class="fas fa-plus"></i> @endif
                            </span>
                            <span class="check-label">{{ $section['label'] }}</span>
                            <i class="fas fa-chevron-right" style="color:var(--muted-2);font-size:12px"></i>
                        </a>
                    @empty
                        <p class="text-muted text-small" style="padding:14px 0">Everything looks great. Your profile is complete!</p>
                    @endforelse
                </div>
            </div>

            <div class="card card-pad">
                <div class="flex items-center justify-between" style="margin-bottom:16px;gap:10px;flex-wrap:wrap">
                    <h3 class="card-title" style="margin:0"><i class="fas fa-users-viewfinder"></i> Who Viewed You</h3>
                    <a href="{{ route('discover.index') }}" class="text-small" style="color:var(--brand);font-weight:600">See all</a>
                </div>
                <div class="vlist">
                    @forelse ($recent_viewers as $view)
                        @php($viewer = $view->viewer)
                        <a href="{{ route('profiles.show', $viewer) }}" class="vlist-row">
                            <x-frontend::avatar :user="$viewer" :size="46" :showOnline="true" />
                            <div style="flex:1;min-width:0">
                                <div class="vlist-name">{{ $viewer->name }}</div>
                                <div class="text-tiny text-muted">
                                    {{ $viewer->age() ?? '—' }} yrs · {{ $viewer->profile?->district ?? '—' }}
                                </div>
                            </div>
                            <span class="time-ago">{{ $view->viewed_at->diffForHumans() }}</span>
                        </a>
                    @empty
                        <p class="text-muted text-small" style="padding:10px 0">No profile views yet. Try finding matches to get noticed.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="member-col">
            <div class="flex items-center justify-between" style="margin-bottom:16px;gap:10px;flex-wrap:wrap">
                <h3 class="card-title" style="margin:0"><i class="fas fa-fire"></i> Recommended For You</h3>
                <a href="{{ route('discover.index') }}" class="text-small" style="color:var(--brand);font-weight:600">Browse all</a>
            </div>

            @if ($recommended->isNotEmpty())
                <div class="grid-3" style="--cols:3">
                    @foreach ($recommended->take(6) as $candidate)
                        <x-frontend::profile-card :user="$candidate" :score="$scores[$candidate->id] ?? null" :showActions="false" />
                    @endforeach
                </div>
            @else
                <div class="card card-pad" style="text-align:center;padding:48px 24px">
                    <div class="es-ico" style="font-size:30px;color:var(--brand)"><i class="fas fa-fire"></i></div>
                    <p class="text-muted text-small mb-4">No recommendations yet. Set your partner preferences and we'll surface your best matches.</p>
                    <a href="{{ route('settings.preference') }}" class="btn btn-primary btn-sm">Set Preferences</a>
                </div>
            @endif

            @if ($recent_interests->isNotEmpty())
                <div class="card card-pad" style="margin-top:24px">
                    <h3 class="card-title"><i class="fas fa-envelope-open-text"></i> Recent Interest Requests</h3>
                    <div class="vlist">
                        @foreach ($recent_interests as $interest)
                            @php($sender = $interest->sender)
                            <div class="vlist-row">
                                <x-frontend::avatar :user="$sender" :size="46" :showOnline="true" />
                                <div style="flex:1;min-width:0">
                                    <div class="vlist-name">{{ $sender->name }}</div>
                                    <div class="text-tiny text-muted">{{ $interest->created_at->diffForHumans() }} · {{ $interest->statusLabel() }}</div>
                                </div>
                                <a href="{{ route('interests.received') }}" class="btn btn-xs btn-brand-soft">Respond</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection