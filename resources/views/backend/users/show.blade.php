@extends('backend.layouts.app')

@php
    use App\Support\Media;
    use App\Support\Reference;

    $profile = $user->profile;

    $badge = [
        'active' => 'badge-success',
        'pending' => 'badge-warning',
        'suspended' => 'badge-danger',
        'inactive' => 'badge-muted',
    ];
    $isVerified = ($profile?->verification_status ?? '') === 'verified';
    $completion = (int) ($profile?->profile_completion ?? 0);

    // Small helpers so the dozens of optional fields below read as one line each.
    $dash = fn ($value) => filled($value) ? $value : '—';
    $chips = fn ($items) => collect($items ?? [])->filter()->values();
@endphp

@section('title', $user->name)
@section('crumb', 'Members · '.$user->name)

@section('content')
    @if (session('success'))
        <div class="alert alert-success" data-auto-close><i class="fas fa-circle-check"></i> {{ session('success') }}</div>
    @endif

    {{-- Identity band. The whole record at a glance before the detail cards, and
         the three actions an admin reaches for from here. --}}
    <div class="ud-hero">
        <div class="ud-hero-inner">
            <span class="ud-avatar">
                <img src="{{ $user->primaryPhoto?->url() ?? Media::avatar($user->name) }}" alt="" onerror="this.style.display='none'">
                <span class="initials">{{ $user->initials }}</span>
            </span>

            <div class="ud-hero-body">
                <h2 class="ud-hero-name">
                    {{ $user->name }}
                    @if ($isVerified)
                        <i class="fas fa-circle-check" style="font-size:17px;color:var(--accent)" title="Verified" aria-label="Verified"></i>
                    @endif
                </h2>

                <div class="ud-hero-badges">
                    <span class="badge {{ $badge[$user->status] ?? 'badge-muted' }}">{{ ucfirst($user->status) }}</span>
                    @if ($isVerified)
                        <span class="badge badge-success"><i class="fas fa-shield-halved"></i> Verified</span>
                    @else
                        <span class="badge badge-muted"><i class="fas fa-shield"></i> Not verified</span>
                    @endif
                    @if ($user->is_admin)
                        <span class="badge badge-accent"><i class="fas fa-crown"></i> Admin</span>
                    @else
                        <span class="badge badge-info">Member</span>
                    @endif
                    @if ($profile)
                        <span class="badge badge-{{ $profile->statusTone() }}"><i class="fas {{ $profile->statusIcon() }}"></i> {{ $profile->statusLabel() }}</span>
                    @else
                        <span class="badge badge-warning"><i class="fas fa-hourglass-half"></i> No profile record</span>
                    @endif
                </div>

                <p class="ud-hero-sub">
                    &#64;{{ $user->username }}<span class="dot">•</span>{{ $user->email }}
                </p>

                <ul class="ud-hero-meta">
                    <li><i class="fas fa-phone"></i> {{ $dash($user->phone) }}</li>
                    <li><i class="fas fa-location-dot"></i> {{ $profile?->locationLabel() ?? '—' }}</li>
                    <li><i class="fas fa-calendar-plus"></i> Joined {{ $user->created_at?->format('d M Y') ?? '—' }}</li>
                    <li><i class="fas fa-clock"></i> Last seen {{ $user->lastSeenLabel() }}</li>
                </ul>
            </div>

            <div class="ud-hero-side">
                <div class="ud-meter">
                    <div class="ud-meter-head"><span>Profile completion</span><strong>{{ $completion }}%</strong></div>
                    <div class="ud-meter-bar"><span style="width: {{ $completion }}%"></span></div>
                </div>

                <div class="ud-hero-actions">
                    <a href="{{ route('backend.users.details', $user) }}" class="btn btn-accent btn-sm"><i class="fas fa-pen-to-square"></i> Edit all details</a>
                    <a href="{{ route('backend.users.edit', $user) }}" class="btn btn-outline btn-sm"><i class="fas fa-pen"></i> Edit account</a>
                    <a href="{{ route('profiles.show', $user) }}" target="_blank" rel="noopener" class="btn btn-ghost btn-sm"><i class="fas fa-arrow-up-right-from-square"></i> Public profile</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Activity tiles --}}
    <div class="grid ud-stats mb-5">
        @foreach ([
            ['n' => number_format($activity['interests_sent']), 'l' => 'Interests sent', 'i' => 'fa-heart', 'c' => 'ic-brand'],
            ['n' => number_format($activity['interests_received']), 'l' => 'Interests received', 'i' => 'fa-envelope-open-text', 'c' => 'ic-accent'],
            ['n' => number_format($activity['profile_views']), 'l' => 'Profile views', 'i' => 'fa-eye', 'c' => 'ic-info'],
            ['n' => number_format($activity['reports']), 'l' => 'Reports received', 'i' => 'fa-flag', 'c' => 'ic-danger'],
        ] as $a)
            <div class="card stat-tile">
                <span class="st-ico {{ $a['c'] }}"><i class="fas {{ $a['i'] }}"></i></span>
                <span class="st-num">{{ $a['n'] }}</span>
                <span class="st-lbl">{{ $a['l'] }}</span>
            </div>
        @endforeach
    </div>

    <div class="ud-grid">
        {{-- Main column: the profile the member filled in, section by section --}}
        <div class="ud-main">
            <div class="card">
                <div class="ud-card-head">
                    <span class="ud-card-ico ic-brand"><i class="fas fa-user"></i></span>
                    <h3>Basic information</h3>
                    <a href="{{ route('backend.users.details', $user) }}#details-basic" class="btn btn-ghost btn-sm ud-edit"><i class="fas fa-pen"></i> Edit</a>
                </div>
                <div class="ud-card-body">
                    @if ($profile)
                        <dl class="pf-facts">
                            <div><dt>Looking as</dt><dd>{{ Reference::label($profile->gender, 'gender') }}</dd></div>
                            <div><dt>Birth date</dt><dd>{{ $profile->date_of_birth?->format('d M Y') ?? '—' }}{{ $profile->age() ? ' · '.$profile->age().' yrs' : '' }}</dd></div>
                            <div><dt>Height</dt><dd>{{ $profile->heightLabel() }}</dd></div>
                            <div><dt>Marital status</dt><dd>{{ Reference::label($profile->marital_status, 'marital_status') }}</dd></div>
                            <div><dt>Religion</dt><dd>{{ Reference::label($profile->religion, 'religion') }}</dd></div>
                            <div><dt>Mother tongue</dt><dd>{{ $dash($profile->mother_tongue) }}</dd></div>
                            <div><dt>Country</dt><dd>{{ Reference::label($profile->country, 'country') }}</dd></div>
                            <div><dt>Visibility</dt><dd>{{ $profile->visibilityLabel() }}</dd></div>
                        </dl>

                        @if ($profile->headline)
                            <div class="mt-4"><div class="text-tiny text-muted mb-3">Headline</div><p class="ud-about">{{ $profile->headline }}</p></div>
                        @endif
                        @if ($profile->about_me)
                            <div class="mt-4"><div class="text-tiny text-muted mb-3">About</div><p class="ud-about">{{ $profile->about_me }}</p></div>
                        @endif
                    @else
                        <p class="text-muted" style="margin:0">No profile record yet. <a href="{{ route('backend.users.details', $user) }}">Create one</a>.</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="ud-card-head">
                    <span class="ud-card-ico ic-info"><i class="fas fa-graduation-cap"></i></span>
                    <h3>Education &amp; career</h3>
                    <a href="{{ route('backend.users.details', $user) }}#details-education" class="btn btn-ghost btn-sm ud-edit"><i class="fas fa-pen"></i> Edit</a>
                </div>
                <div class="ud-card-body">
                    <dl class="pf-facts">
                        <div><dt>Highest level</dt><dd>{{ Reference::label($user->education?->level, 'education') }}</dd></div>
                        <div><dt>Degree</dt><dd>{{ $dash($user->education?->degree) }}</dd></div>
                        <div><dt>Field of study</dt><dd>{{ $dash($user->education?->field_of_study) }}</dd></div>
                        <div><dt>Institution</dt><dd>{{ $dash($user->education?->institution) }}</dd></div>
                        <div><dt>Years</dt><dd>{{ $user->education?->start_year || $user->education?->end_year ? trim(($user->education?->start_year ?? '').' – '.($user->education?->end_year ?? ''), ' –') : '—' }}</dd></div>
                        <div><dt>Designation</dt><dd>{{ $dash($user->occupation?->designation) }}</dd></div>
                        <div><dt>Company</dt><dd>{{ $dash($user->occupation?->company) }}</dd></div>
                        <div><dt>Employment</dt><dd>{{ Reference::label($user->occupation?->employment_type, 'employment') }}</dd></div>
                        <div><dt>Income range</dt><dd>{{ Reference::label($user->occupation?->income_range, 'income') }}</dd></div>
                        <div><dt>Work location</dt><dd>{{ $dash($user->occupation?->work_location) }}</dd></div>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="ud-card-head">
                    <span class="ud-card-ico ic-brand"><i class="fas fa-map-location-dot"></i></span>
                    <h3>Location</h3>
                    <a href="{{ route('backend.users.details', $user) }}#details-location" class="btn btn-ghost btn-sm ud-edit"><i class="fas fa-pen"></i> Edit</a>
                </div>
                <div class="ud-card-body">
                    <dl class="pf-facts">
                        <div><dt>Division</dt><dd>{{ $dash($profile?->division) }}</dd></div>
                        <div><dt>District</dt><dd>{{ $dash($profile?->district) }}</dd></div>
                        <div><dt>City / Area</dt><dd>{{ $dash($profile?->city) }}</dd></div>
                        <div><dt>Country</dt><dd>{{ Reference::label($profile?->country, 'country') }}</dd></div>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="ud-card-head">
                    <span class="ud-card-ico ic-accent"><i class="fas fa-people-group"></i></span>
                    <h3>Family</h3>
                    <a href="{{ route('backend.users.details', $user) }}#details-family" class="btn btn-ghost btn-sm ud-edit"><i class="fas fa-pen"></i> Edit</a>
                </div>
                <div class="ud-card-body">
                    <dl class="pf-facts">
                        <div><dt>Family type</dt><dd>{{ Reference::label($user->familyDetail?->family_type, 'family_type') }}</dd></div>
                        <div><dt>Family status</dt><dd>{{ Reference::label($user->familyDetail?->family_status, 'family_status') }}</dd></div>
                        <div><dt>Father's occupation</dt><dd>{{ $dash($user->familyDetail?->father_occupation) }}</dd></div>
                        <div><dt>Mother's occupation</dt><dd>{{ $dash($user->familyDetail?->mother_occupation) }}</dd></div>
                        <div><dt>Siblings</dt><dd>{{ $user->familyDetail?->siblingLabel() ?? '—' }}</dd></div>
                        <div><dt>Family income</dt><dd>{{ Reference::label($user->familyDetail?->family_income_range, 'income') }}</dd></div>
                    </dl>
                    @if ($user->familyDetail?->about_family)
                        <p class="ud-about mt-4">{{ $user->familyDetail->about_family }}</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="ud-card-head">
                    <span class="ud-card-ico ic-success"><i class="fas fa-leaf"></i></span>
                    <h3>Lifestyle</h3>
                    <a href="{{ route('backend.users.details', $user) }}#details-lifestyle" class="btn btn-ghost btn-sm ud-edit"><i class="fas fa-pen"></i> Edit</a>
                </div>
                <div class="ud-card-body">
                    <dl class="pf-facts">
                        <div><dt>Diet</dt><dd>{{ Reference::label($user->lifestyleDetail?->diet, 'diet') }}</dd></div>
                        <div><dt>Smoking</dt><dd>{{ Reference::label($user->lifestyleDetail?->smoking, 'smoking') }}</dd></div>
                        <div><dt>Drinking</dt><dd>{{ Reference::label($user->lifestyleDetail?->drinking, 'drinking') }}</dd></div>
                    </dl>

                    @if ($chips($user->lifestyleDetail?->hobbies)->isNotEmpty())
                        <div class="mt-4">
                            <div class="text-tiny text-muted mb-3">Hobbies</div>
                            <div class="ud-hero-badges" style="margin:0">
                                @foreach ($chips($user->lifestyleDetail?->hobbies) as $hobby)
                                    <span class="badge badge-muted">{{ $hobby }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if ($chips($user->lifestyleDetail?->interests)->isNotEmpty())
                        <div class="mt-4">
                            <div class="text-tiny text-muted mb-3">Interests</div>
                            <div class="ud-hero-badges" style="margin:0">
                                @foreach ($chips($user->lifestyleDetail?->interests) as $interest)
                                    <span class="badge badge-brand">{{ $interest }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if ($user->lifestyleDetail?->about_lifestyle)
                        <p class="ud-about mt-4">{{ $user->lifestyleDetail->about_lifestyle }}</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="ud-card-head">
                    <span class="ud-card-ico ic-brand"><i class="fas fa-hand-holding-heart"></i></span>
                    <h3>Partner preference</h3>
                    <a href="{{ route('backend.users.details', $user) }}#details-preference" class="btn btn-ghost btn-sm ud-edit"><i class="fas fa-pen"></i> Edit</a>
                </div>
                <div class="ud-card-body">
                    @php($pref = $user->partnerPreference)
                    @if ($pref)
                        <dl class="pf-facts">
                            <div><dt>Preferred gender</dt><dd>{{ Reference::label($pref->preferred_gender, 'gender') }}</dd></div>
                            <div><dt>Age range</dt><dd>{{ $pref->ageRangeLabel() }}</dd></div>
                            <div><dt>Height range</dt><dd>{{ $pref->height_min_cm || $pref->height_max_cm ? ($pref->height_min_cm ?: '—').' – '.($pref->height_max_cm ?: '—').' cm' : 'Any height' }}</dd></div>
                            <div><dt>Preferred country</dt><dd>{{ $dash($pref->preferred_country) }}</dd></div>
                            <div><dt>Preferred division</dt><dd>{{ $dash($pref->preferred_division) }}</dd></div>
                            <div><dt>Preferred district</dt><dd>{{ $dash($pref->preferred_district) }}</dd></div>
                            <div><dt>Education</dt><dd>{{ Reference::label($pref->education_level, 'education') }}</dd></div>
                            <div><dt>Profession</dt><dd>{{ $dash($pref->profession) }}</dd></div>
                            <div><dt>Diet</dt><dd>{{ Reference::label($pref->diet, 'diet') }}</dd></div>
                            <div><dt>Smoking</dt><dd>{{ Reference::label($pref->smoking, 'smoking') }}</dd></div>
                            <div><dt>Drinking</dt><dd>{{ Reference::label($pref->drinking, 'drinking') }}</dd></div>
                        </dl>

                        @if ($chips($pref->religions)->isNotEmpty())
                            <div class="mt-4">
                                <div class="text-tiny text-muted mb-3">Religions</div>
                                <div class="ud-hero-badges" style="margin:0">
                                    @foreach ($chips($pref->religions) as $religion)
                                        <span class="badge badge-muted">{{ Reference::label($religion, 'religion') }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if ($chips($pref->marital_statuses)->isNotEmpty())
                            <div class="mt-4">
                                <div class="text-tiny text-muted mb-3">Accepted marital statuses</div>
                                <div class="ud-hero-badges" style="margin:0">
                                    @foreach ($chips($pref->marital_statuses) as $status)
                                        <span class="badge badge-muted">{{ Reference::label($status, 'marital_status') }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if ($pref->notes)
                            <p class="ud-about mt-4">{{ $pref->notes }}</p>
                        @endif
                    @else
                        <p class="text-muted" style="margin:0">No partner preference on record.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Side rail: the moderation controls first, then the evidence and history --}}
        <div class="ud-side">
            <div class="card ud-mod">
                <div class="ud-card-head">
                    <span class="ud-card-ico ic-danger"><i class="fas fa-shield-halved"></i></span>
                    <h3>Moderation</h3>
                </div>
                <div class="ud-card-body">
                <p class="ud-mod-note">
                    Account is <strong>{{ ucfirst($user->status) }}</strong>
                    @if ($profile) · profile is <strong>{{ $profile->statusLabel() }}</strong>@endif.
                </p>

                @unless ($isVerified)
                    <form method="POST" action="{{ route('backend.users.verify', $user) }}"
                          data-confirm-title="Approve and verify?"
                          data-confirm="{{ $user->name }} will get a verified badge and full access to messaging."
                          data-confirm-ok="Approve &amp; verify" data-confirm-icon="success"
                          data-confirm-color="#16A34A" data-confirm-focus-cancel>
                        @csrf
                        <button class="btn btn-success-soft btn-sm"><i class="fas fa-check"></i> Approve &amp; verify profile</button>
                    </form>
                @endunless

                @if ($user->status !== 'suspended' && ! $user->is_admin)
                    <form method="POST" action="{{ route('backend.users.suspend', $user) }}"
                          data-confirm-title="Suspend {{ $user->name }}?"
                          data-confirm="They are signed out, blocked from the site, and disappear from search results."
                          data-confirm-ok="Suspend member" data-confirm-icon="error"
                          data-confirm-color="#DC2626">
                        @csrf
                        <button class="btn btn-danger-soft btn-sm"><i class="fas fa-ban"></i> Suspend</button>
                    </form>
                @elseif ($user->status === 'suspended')
                    <form method="POST" action="{{ route('backend.users.activate', $user) }}"
                          data-confirm-title="Reactivate {{ $user->name }}?"
                          data-confirm="The member signs in again and appears in search results straight away."
                          data-confirm-ok="Reactivate" data-confirm-icon="question"
                          data-confirm-color="#16A34A" data-confirm-focus-cancel>
                        @csrf
                        <button class="btn btn-success-soft btn-sm"><i class="fas fa-rotate-left"></i> Reactivate</button>
                    </form>
                @endif

                @if (! $user->is_admin)
                    <form method="POST" action="{{ route('backend.users.destroy', $user) }}"
                          data-confirm-title="Delete {{ $user->name }} permanently?"
                          data-confirm="The member, their messages, photos and reports are removed. This cannot be undone."
                          data-confirm-ok="Yes, delete everything" data-confirm-icon="error"
                          data-confirm-color="#DC2626">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-sm" style="color:var(--danger)"><i class="fas fa-trash"></i> Delete member</button>
                    </form>
                @endif
                </div>
            </div>

            <div class="card">
                <div class="ud-card-head">
                    <span class="ud-card-ico ic-brand"><i class="fas fa-camera"></i></span>
                    <h3>Photos</h3>
                    <a href="{{ route('backend.users.details', $user) }}#details-photo" class="btn btn-outline btn-sm ud-edit"><i class="fas fa-plus"></i> Add</a>
                </div>
                <div class="ud-card-body">
                    @if ($user->photos->isEmpty())
                        <p class="text-muted" style="margin:0">No photos uploaded.</p>
                    @else
                        <div class="ud-gallery">
                            @foreach ($user->photos as $photo)
                                <div class="ud-shot {{ $photo->is_primary ? 'is-primary' : '' }}">
                                    <a href="{{ $photo->url() }}" target="_blank" rel="noopener" aria-label="Open photo {{ $loop->iteration }} of {{ $user->name }} at full size">
                                        <img src="{{ $photo->url() }}" alt="" loading="lazy">
                                    </a>
                                    @if ($photo->is_primary)
                                        <span class="ud-shot-tag">Primary</span>
                                    @endif
                                    {{-- The button sits top-left because the gold Primary
                                         tag owns the top-right corner. --}}
                                    <form method="POST" action="{{ route('backend.users.photos.destroy', [$user, $photo]) }}"
                                          data-confirm-title="Remove this photo?"
                                          data-confirm="{{ $photo->is_primary ? 'It disappears from the member\'s gallery for good. Since it is the primary photo, the next one takes over as their profile picture.' : 'It disappears from the member\'s gallery for good.' }}"
                                          data-confirm-ok="Remove photo" data-confirm-icon="error"
                                          data-confirm-color="#DC2626">
                                        @csrf @method('DELETE')
                                        <button class="ud-shot-x" aria-label="Remove photo {{ $loop->iteration }} from the gallery">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="ud-card-head">
                    <span class="ud-card-ico ic-info"><i class="fas fa-id-card-clip"></i></span>
                    <h3>Identity verifications</h3>
                </div>
                <div class="ud-card-body">
                    @forelse ($user->verifications as $v)
                        <div class="info-row">
                            <i class="fas fa-shield-halved"></i>
                            <div style="min-width:0;flex:1">
                                <div class="ir-value">
                                    {{ ucfirst(str_replace('_', ' ', $v->type)) }}
                                    <span class="badge badge-{{ $v->status === 'approved' ? 'success' : ($v->status === 'rejected' ? 'danger' : 'warning') }}" style="font-size:10.5px">{{ ucfirst($v->status) }}</span>
                                </div>
                                <div class="ir-label">Submitted {{ $v->created_at?->diffForHumans() }} · {{ $v->admin_note ? 'Note: '.$v->admin_note : 'No note' }}</div>
                            </div>
                            @if ($v->document_path)
                                <a href="{{ route('backend.verification.document', $v) }}" target="_blank" class="btn btn-soft btn-sm"><i class="fas fa-file"></i> Doc</a>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted" style="margin:0">No identity verification requests.</p>
                    @endforelse
                </div>
            </div>

            <div class="card">
                <div class="ud-card-head">
                    <span class="ud-card-ico ic-danger"><i class="fas fa-flag"></i></span>
                    <h3>Reports received</h3>
                </div>
                <div class="ud-card-body">
                    @forelse ($user->reportsReceived as $r)
                        <div class="info-row">
                            <i class="fas fa-flag"></i>
                            <div style="min-width:0;flex:1">
                                <div class="ir-value">{{ ucwords(str_replace('_', ' ', $r->reason)) }}</div>
                                <div class="ir-label">By {{ $r->reporter?->name }} · {{ $r->created_at?->diffForHumans() }}</div>
                            </div>
                            <a href="{{ route('backend.reports.show', $r) }}" class="btn btn-outline btn-sm" aria-label="Open report"><i class="fas fa-eye"></i></a>
                        </div>
                    @empty
                        <p class="text-muted" style="margin:0">No reports received.</p>
                    @endforelse
                </div>
            </div>

            <div class="card">
                <div class="ud-card-head">
                    <span class="ud-card-ico ic-warning"><i class="fas fa-flag"></i></span>
                    <h3>Reports made</h3>
                </div>
                <div class="ud-card-body">
                    @forelse ($user->reportsMade as $r)
                        <div class="info-row">
                            <i class="fas fa-flag"></i>
                            <div style="min-width:0;flex:1">
                                <div class="ir-value">{{ ucwords(str_replace('_', ' ', $r->reason)) }}</div>
                                <div class="ir-label">Against {{ $r->reportedUser?->name }} · {{ $r->created_at?->diffForHumans() }}</div>
                            </div>
                            <a href="{{ route('backend.reports.show', $r) }}" class="btn btn-outline btn-sm" aria-label="Open report"><i class="fas fa-eye"></i></a>
                        </div>
                    @empty
                        <p class="text-muted" style="margin:0">No reports made.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
