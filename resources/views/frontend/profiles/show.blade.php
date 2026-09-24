@extends('frontend.layouts.app')

@section('title', $profile->name."'s Profile")

@section('content')
@php
    $p = $profile->profile;
    $primaryPhoto = $photos->firstWhere('is_primary', true) ?? $photos->first();
    $photoSrc = $primaryPhoto ? \App\Support\Media::url($primaryPhoto->path, $profile->name) : $profile->photoUrl();
    $isAvatar = \Str::contains($photoSrc, '/media/avatar/');
    $viewer = auth()->user();
    $myInterest = $interest && $viewer && $interest->sender_id === $viewer->id;
    $theirInterest = $interest && $viewer && $interest->receiver_id === $viewer->id;
    $eduLabel = \App\Support\Reference::educationLevels()[$profile->education?->level] ?? null;
@endphp

<div class="container profile-page">
    <nav class="crumbs" aria-label="Breadcrumb">
        <a href="{{ route('discover.index') }}"><i class="fas fa-arrow-left"></i> Back to Discover</a>
    </nav>

    <div class="profile-layout">
        {{-- Left / main column --}}
        <div class="profile-main">
            {{-- Gallery --}}
            <div class="card gallery">
                <div class="gallery-main">
                    @if ($isAvatar)
                        <div class="gallery-fill-img" data-photo-main>
                            <span>{{ $profile->initials }}</span>
                        </div>
                    @else
                        <img src="{{ $photoSrc }}" alt="{{ $profile->name }}" data-photo-main>
                    @endif

                    @if ($p?->verification_status === 'verified')
                        <span class="gallery-badge"><span class="badge badge-info"><i class="fas fa-circle-check"></i> Verified Profile</span></span>
                    @endif

                    @if ($profile->isOnline())
                        <span class="gallery-badge" style="right:auto;left:16px"><span class="badge badge-success"><i class="fas fa-circle" style="font-size:8px"></i> Online now</span></span>
                    @endif
                </div>

                @if ($photos->count() > 1)
                    <div class="gallery-thumbs">
                        @foreach ($photos as $photo)
                            <button type="button" class="ph-thumb {{ $photo->is_primary ? 'active' : '' }}"
                                    data-full="{{ \App\Support\Media::url($photo->path, $profile->name) }}"
                                    data-caption="{{ $profile->name }}">
                                <img src="{{ \App\Support\Media::url($photo->path, $profile->name) }}" alt="Photo">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Header --}}
            <div class="card card-pad profile-head">
                <div class="flex items-center gap-4" style="gap:16px;flex-wrap:wrap">
                    <div style="flex:1;min-width:0">
                        <h1 class="profile-name">
                            {{ $profile->name }}
                            @if ($profile->isVerifiedProfile()) <i class="fas fa-circle-check" style="color:#2563eb;font-size:17px" title="Verified"></i> @endif
                        </h1>
                        @if ($p?->headline)
                            <p class="text-muted" style="margin:2px 0 8px">{{ $p->headline }}</p>
                        @endif
                        <div class="pcard-meta" style="gap:14px">
                            @if ($p)
                                <span><i class="fas fa-cake-candles"></i> {{ $p->ageGroup() }}</span>
                                <span><i class="fas fa-ruler-vertical"></i> {{ $p->heightLabel() }}</span>
                                <span><i class="fas fa-location-dot"></i> {{ $p->locationLabel() }}</span>
                                <span><i class="fas fa-eye"></i> {{ $viewCount }} profile views</span>
                            @endif
                        </div>
                    </div>
                    @if ($profile->isOnline())
                        <div class="text-tiny text-muted" style="text-align:right">Online now</div>
                    @else
                        <div class="text-tiny text-muted" style="text-align:right">Last seen {{ $profile->lastSeenLabel() }}</div>
                    @endif
                </div>
            </div>

            {{-- About --}}
            @if ($p?->about_me)
                <div class="card card-pad">
                    <h3 class="card-title"><i class="fas fa-quote-left"></i> About {{ \Illuminate\Support\Str::before($profile->name, ' ') }}</h3>
                    <p style="white-space:pre-wrap;color:var(--muted);line-height:1.75">{{ $p->about_me }}</p>
                </div>
            @endif

            {{-- Biography --}}
            @include('frontend.profiles.partials.biography')

            {{-- Similar rail --}}
            @if ($similar->isNotEmpty())
                <div style="margin-top:26px">
                    <div class="flex items-center justify-between" style="margin-bottom:14px;gap:10px;flex-wrap:wrap">
                        <h3 class="card-title" style="margin:0"><i class="fas fa-people-arrows"></i> Similar Profiles</h3>
                        <a href="{{ route('discover.index') }}" class="text-small" style="color:var(--brand);font-weight:600">See all</a>
                    </div>
                    <div class="grid-3 cards-grid">
                        @foreach ($similar as $candidate)
                            <x-frontend::profile-card :user="$candidate" :showActions="false" />
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Right / action column --}}
        <aside class="profile-side">
            {{-- Match card --}}
            @if ($score !== null)
                <div class="card card-pad match-card">
                    <div class="score-ring" style="--p: {{ $score['percentage'] }}%">
                        <span class="score-num">{{ $score['percentage'] }}%</span>
                        <span class="score-lbl">Match</span>
                    </div>

                    @if ($mutualMatch)
                        <div class="mutual-banner"><i class="fas fa-heart"></i> Mutual match — say hello!</div>
                    @endif

                    @if (! empty($score['reasons']))
                        <div class="reason-list">
                            @foreach (array_slice($score['reasons'], 0, 4) as $reason)
                                <div class="reason-row">
                                    <i class="fas fa-check"></i>
                                    <div>
                                        <div class="reason-label">{{ $reason['label'] }}</div>
                                        <div class="text-tiny text-muted">{{ $reason['detail'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-ghost btn-sm btn-block" data-modal-open="scoreBreakdown">
                            View full breakdown <i class="fas fa-chevron-down"></i>
                        </button>
                    @endif
                </div>
            @endif

            {{-- Actions --}}
            <div class="card card-pad actions-card">
                @auth
                    @if ($theirInterest && $interest->isPending())
                        <div class="alert" style="margin-bottom:14px">
                            <i class="fas fa-envelope"></i>
                            <div><strong>{{ $profile->name }}</strong> sent you an interest.</div>
                        </div>
                        <div class="flex gap-2" style="gap:10px">
                            <form method="POST" action="{{ route('interests.accept', $interest) }}" style="flex:1">
                                @csrf
                                <button class="btn btn-primary btn-block">Accept</button>
                            </form>
                            <form method="POST" action="{{ route('interests.reject', $interest) }}" style="flex:1">
                                @csrf
                                <button class="btn btn-ghost btn-block">Decline</button>
                            </form>
                        </div>
                    @elseif ($myInterest && $interest->isPending())
                        <button class="btn btn-soft btn-block btn-lg" disabled>
                            <i class="fas fa-paper-plane"></i> Interest Sent
                        </button>
                        <form method="POST" action="{{ route('interests.cancel', $interest) }}" style="margin-top:10px">
                            @csrf
                            <button class="btn btn-ghost btn-block text-muted" data-confirm="Withdraw your interest?">Withdraw Interest</button>
                        </form>
                    @elseif ($interest && $interest->isAccepted())
                        @if ($canMessage)
                            <form method="POST" action="{{ route('messages.start', $profile) }}">
                                @csrf
                                <button class="btn btn-primary btn-block btn-lg"><i class="fas fa-comments"></i> Start Conversation</button>
                            </form>
                        @else
                            <div class="alert"><i class="fas fa-lock"></i><div>Messaging is unavailable for this profile.</div></div>
                        @endif
                        <a href="{{ route('interests.accepted') }}" class="btn btn-ghost btn-block" style="margin-top:10px">View Accepted Interests</a>
                    @elseif ($canInteract && $canMessage)
                        <form method="POST" action="{{ route('interests.store', $profile) }}">
                            @csrf
                            <button class="btn btn-primary btn-block btn-lg"><i class="fas fa-heart"></i> Send Interest</button>
                        </form>
                    @else
                        <div class="alert"><i class="fas fa-info-circle"></i><div>This profile cannot be interacted with right now.</div></div>
                    @endif

                    <form method="POST" action="{{ $isShortlisted ? route('favorites.destroy', $profile) : route('favorites.store', $profile) }}" style="margin-top:10px">
                        @csrf
                        @if ($isShortlisted) @method('DELETE') @endif
                        <button class="btn btn-ghost btn-block">
                            <i class="{{ $isShortlisted ? 'fas' : 'far' }} fa-star"></i>
                            {{ $isShortlisted ? 'Shortlisted' : 'Add to Shortlist' }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-block btn-lg"><i class="fas fa-heart"></i> Login to Show Interest</a>
                    <a href="{{ route('register') }}" class="btn btn-ghost btn-block" style="margin-top:10px">Create Free Profile</a>
                @endauth

                @auth
                    <div class="action-links">
                        <button type="button" data-modal-open="reportModal"><i class="fas fa-flag"></i> Report Profile</button>
                        @if (! $isBlocked)
                            <form method="POST" action="{{ route('blocks.store', $profile) }}" data-confirm="Block {{ $profile->name }}? You will no longer see each other.">
                                @csrf
                                <button type="submit"><i class="fas fa-ban"></i> Block User</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('blocks.destroy', $profile) }}">
                                @csrf @method('DELETE')
                                <button type="submit"><i class="fas fa-unlock"></i> Unblock User</button>
                            </form>
                        @endif
                    </div>
                @endauth
            </div>

            {{-- Partner preference summary --}}
            @php($pref = $profile->partnerPreference)
            @if ($pref)
                <div class="card card-pad">
                    <h3 class="card-title"><i class="fas fa-bullseye"></i> Seeking</h3>
                    <ul class="mini-list">
                        @if ($pref->preferred_gender)
                            <li><span>Looking for</span><strong>{{ \App\Support\Reference::genders()[$pref->preferred_gender] ?? ucfirst($pref->preferred_gender) }}</strong></li>
                        @endif
                        @if ($pref->age_min || $pref->age_max)
                            <li><span>Age</span><strong>{{ $pref->age_min ?: 18 }} – {{ $pref->age_max ?: 80 }} yrs</strong></li>
                        @endif
                        @if ($pref->preferred_division)
                            <li><span>Location</span><strong>{{ $pref->preferred_division }}</strong></li>
                        @endif
                        @if (! empty($pref->religions))
                            <li><span>Religion</span><strong>{{ implode(', ', array_map(fn ($r) => \App\Support\Reference::religions()[$r] ?? ucfirst($r), $pref->religions)) }}</strong></li>
                        @endif
                        @if ($pref->education_level)
                            <li><span>Education</span><strong>{{ \App\Support\Reference::educationLevels()[$pref->education_level] ?? ucfirst($pref->education_level) }}</strong></li>
                        @endif
                        @if ($pref->profession)
                            <li><span>Profession</span><strong>{{ $pref->profession }}</strong></li>
                        @endif
                        @if ($pref->notes)
                            <li style="display:block"><span style="display:block;margin-bottom:4px">Notes</span><em class="text-muted text-small" style="font-style:normal">{{ $pref->notes }}</em></li>
                        @endif
                    </ul>
                </div>
            @endif
        </aside>
    </div>
</div>

{{-- Report modal --}}
@auth
    <div class="modal" id="reportModal" aria-hidden="true">
        <div class="modal-backdrop"></div>
        <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="reportTitle">
            <div class="modal-head">
                <h3 id="reportTitle">Report {{ $profile->name }}</h3>
                <button type="button" class="btn-icon" data-modal-close><i class="fas fa-xmark"></i></button>
            </div>
            <form method="POST" action="{{ route('reports.store', $profile) }}">
                @csrf
                <div class="field">
                    <label>Reason</label>
                    @foreach (\App\Models\Report::REASONS as $key => $label)
                        <label class="radio-line">
                            <input type="radio" name="reason" value="{{ $key }}" required>
                            {{ $label }}
                        </label>
                    @endforeach
                    @error('reason') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label for="report-desc">Details <span class="text-muted">(required for "Other")</span></label>
                    <textarea name="description" id="report-desc" class="input" maxlength="1000" rows="3"
                              placeholder="Tell us what happened.">{{ old('description') }}</textarea>
                    @error('description') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <label class="checkbox">
                    <input type="checkbox" name="block_user" value="1">
                    Also block this member
                </label>
                <div class="modal-foot">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-danger">Submit Report</button>
                </div>
            </form>
        </div>
    </div>
@endauth

{{-- Score breakdown modal --}}
@auth
    @if ($score !== null && ! empty($score['breakdown']))
        <div class="modal" id="scoreBreakdown" aria-hidden="true">
            <div class="modal-backdrop"></div>
            <div class="modal-box" role="dialog" aria-modal="true">
                <div class="modal-head">
                    <h3>Compatibility Breakdown</h3>
                    <button type="button" class="btn-icon" data-modal-close><i class="fas fa-xmark"></i></button>
                </div>
                <div class="breakdown">
                    @foreach ($score['breakdown'] as $item)
                        <div class="bd-row">
                            <div class="bd-top">
                                <span class="bd-label">{{ $item['label'] }}</span>
                                <span class="bd-val {{ $item['matched'] ? 'yes' : 'no' }}">
                                    <i class="fas {{ $item['matched'] ? 'fa-check' : 'fa-times' }}"></i>
                                    {{ $item['matched'] ? '+'.$item['weight'] : '0' }}
                                </span>
                            </div>
                            <div class="bd-bar"><span style="width:{{ $item['matched'] ? 100 : 0 }}%"></span></div>
                            <div class="text-tiny text-muted">{{ $item['detail'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@endauth
@endsection