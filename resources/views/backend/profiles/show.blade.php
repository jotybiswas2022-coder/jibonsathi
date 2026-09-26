@extends('backend.layouts.app')

@php
    use App\Support\Reference;

    $member = $profile->user;
    $openCase = $profile->isOpen();
    $photos = $member?->photos->filter(fn ($ph) => $ph->isApproved()) ?? collect();
@endphp

@section('title', 'Review — '.($member?->name ?? 'Profile'))
@section('crumb', 'Profiles · Case review')

@section('content')
    <div class="page-head">
        <div class="page-head-text">
            <h2>Profile review</h2>
            <p>Read what the member filled in, check the photos, then record the decision. The member is notified of anything other than a move back to pending.</p>
        </div>
        <div class="page-head-actions">
            <a href="{{ route('backend.profiles.index', request()->query()) }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to queue
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" data-auto-close><i class="fas fa-circle-check"></i> {{ session('success') }}</div>
    @endif

    @if ($profile->isThin())
        <div class="alert alert-warning pf-thin-alert">
            <i class="fas fa-triangle-exclamation"></i>
            <div>
                <strong>This profile is only {{ $profile->profile_completion }}% complete.</strong>
                There is very little here to judge. Approving it publishes an almost empty profile to other members.
            </div>
        </div>
    @endif

    {{-- Two columns that collapse on a phone. This used to be an inline
         "300px 1fr" with a sticky rail, which the narrow screen could not
         honour and which pinned a 300px column beside a wide one on a laptop. --}}
    <div class="pf-case">
        <div class="pf-case-main">
            <div class="card card-pad">
                <div class="pf-case-head">
                    <h3 class="pf-case-title">
                        <span class="pf-case-ico"><i class="fas fa-id-card"></i></span>
                        {{ $member?->name ?? 'Deleted user' }}
                    </h3>
                    <span class="badge badge-{{ $profile->statusTone() }}" style="font-size:13px">
                        <i class="fas fa-circle"></i> {{ $profile->statusLabel() }}
                    </span>
                </div>

                <ul class="pf-case-meta">
                    <li><i class="fas fa-clock"></i> Joined {{ $profile->created_at?->diffForHumans() ?? 'unknown' }}</li>
                    <li><i class="fas fa-calendar"></i> {{ $profile->created_at?->format('d M Y, H:i') ?? '—' }}</li>
                    <li><i class="fas fa-map-location-dot"></i> {{ $profile->locationLabel() }}</li>
                    <li>
                        <i class="fas fa-shield-halved"></i>
                        {{ $profile->verificationLabel() }}
                    </li>
                    @if ($profile->approved_at)
                        <li><i class="fas fa-circle-check"></i> Approved {{ $profile->approved_at->diffForHumans() }}</li>
                    @endif
                </ul>

                <div class="pf-meter pf-meter-lg">
                    <div class="pf-meter-head">
                        <span>Profile completion</span>
                        <strong class="pf-{{ $profile->completionTone() }}">{{ $profile->profile_completion }}%</strong>
                    </div>
                    <div class="progress pf-meter-bar">
                        <span class="pf-{{ $profile->completionTone() }}" style="width:{{ $profile->profile_completion }}%"></span>
                    </div>
                </div>
            </div>

            {{-- Photos sit in the wide column, not the rail: judging whether a
                 picture belongs on the site needs the pixels, and three of them
                 across a 330px rail left 89px thumbnails. --}}
            <div class="card card-pad pf-gallery">
                <h3 class="card-title"><i class="fas fa-images"></i> Photos ({{ $photos->count() }})</h3>
                @if ($photos->isEmpty())
                    <p class="text-muted text-small" style="margin:0">
                        No approved photos. A profile with no pictures is the other half of a thin profile.
                    </p>
                @else
                    <p class="hint" style="margin:0 0 12px">Only approved photos show to other members. Removing one here takes it off the site for good.</p>
                    <div class="pf-gallery-grid">
                        @foreach ($photos as $photo)
                            <div class="pf-shot">
                                <a href="{{ $photo->url() }}" target="_blank" rel="noopener" aria-label="Open photo {{ $loop->iteration }} of {{ $member?->name }} at full size">
                                    <img src="{{ $photo->url() }}" alt="" loading="lazy">
                                </a>
                                <form method="POST" action="{{ route('backend.profiles.photos.destroy', [$profile, $photo]) }}"
                                      data-confirm-title="Remove this photo?"
                                      data-confirm="It disappears from the member's gallery. The member can upload it again later."
                                      data-confirm-ok="Remove photo" data-confirm-icon="error"
                                      data-confirm-color="#DC2626">
                                    @csrf @method('DELETE')
                                    <button class="pf-shot-x" aria-label="Remove photo {{ $loop->iteration }} from the gallery">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="card card-pad">
                <h3 class="card-title"><i class="fas fa-user"></i> Member information</h3>
                <dl class="pf-facts">
                    <div>
                        <dt>Gender</dt>
                        <dd>{{ ucfirst($profile->gender ?? '—') }}</dd>
                    </div>
                    <div>
                        <dt>Age</dt>
                        <dd>{{ $profile->ageGroup() }}</dd>
                    </div>
                    <div>
                        <dt>Height</dt>
                        <dd>{{ $profile->heightLabel() }}</dd>
                    </div>
                    <div>
                        <dt>Marital status</dt>
                        <dd>{{ Reference::label($profile->marital_status, 'marital_status') }}</dd>
                    </div>
                    <div>
                        <dt>Religion</dt>
                        <dd>{{ Reference::label($profile->religion, 'religion') }}</dd>
                    </div>
                    <div>
                        <dt>Mother tongue</dt>
                        <dd>{{ $profile->mother_tongue ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt>Education</dt>
                        <dd>{{ $member?->education?->level ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Occupation</dt>
                        <dd>{{ $member?->occupation?->designation ?: ($member?->occupation?->occupation ?? '—') }}</dd>
                    </div>
                    <div>
                        <dt>Family type</dt>
                        <dd>{{ Reference::label($member?->familyDetail?->family_type, 'family_type') }}</dd>
                    </div>
                    <div>
                        <dt>Email</dt>
                        <dd>{{ $member?->email ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Phone</dt>
                        <dd>{{ $member?->phone ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Visibility</dt>
                        <dd>{{ $profile->visibilityLabel() }}</dd>
                    </div>
                </dl>
            </div>

            @if ($profile->headline || $profile->about_me)
                <div class="card card-pad">
                    <h3 class="card-title"><i class="fas fa-quote-left"></i> In their own words</h3>
                    @if ($profile->headline)
                        <p class="pf-headline">{{ $profile->headline }}</p>
                    @endif
                    @if ($profile->about_me)
                        <div class="pf-quote-body">{{ $profile->about_me }}</div>
                    @endif
                </div>
            @endif

            @if ($member?->lifestyleDetail?->about_lifestyle)
                <div class="card card-pad">
                    <h3 class="card-title"><i class="fas fa-leaf"></i> About their lifestyle</h3>
                    <div class="pf-quote-body">{{ $member->lifestyleDetail->about_lifestyle }}</div>
                </div>
            @endif

            @if ($member?->verifications?->isNotEmpty())
                <div class="card card-pad">
                    <h3 class="card-title"><i class="fas fa-shield-halved"></i> Identity verification</h3>
                    <p class="hint" style="margin:0 0 6px">An unverified profile can still be approved, but the badge is what other members trust.</p>
                    @foreach ($member->verifications as $v)
                        <div class="pf-verif-row">
                            <span class="pf-verif-ico"><i class="fas {{ $v->typeIcon() }}"></i></span>
                            <div class="pf-verif-main">
                                <div class="pf-verif-val">
                                    {{ $v->typeLabel() }}
                                    <span class="badge badge-{{ $v->statusTone() }}">{{ $v->statusLabel() }}</span>
                                </div>
                                <div class="pf-verif-sub">
                                    {{ $v->admin_note ? $v->admin_note : 'No admin note' }} · sent {{ $v->created_at?->diffForHumans() }}
                                </div>
                            </div>
                            @if ($v->hasDocument())
                                <a href="{{ route('backend.verification.document', $v) }}" target="_blank" rel="noopener" class="st-act"
                                   title="Open document" aria-label="Open the {{ $v->typeShortLabel() }} document in a new tab">
                                    <i class="fas fa-file-lines"></i>
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="pf-case-side">
            <div class="card card-pad pf-subject">
                <span class="avatar pf-subject-avatar">
                    @if ($member?->primaryPhoto)
                        <img src="{{ $member->primaryPhoto->url() }}" alt="" onerror="this.style.display='none'">
                    @else
                        <span class="initials" style="font-size:26px">{{ $member?->initials ?? 'J' }}</span>
                    @endif
                </span>
                <h3 class="pf-subject-name">{{ $member?->name ?? 'Deleted user' }}</h3>
                <p class="text-muted text-small pf-subject-mail">{{ $member?->email ?? 'No longer on Jibon Sathi' }}</p>
                <p class="text-tiny text-muted pf-subject-loc">{{ $profile->locationLabel() }}</p>
                @if ($member)
                    <a href="{{ route('backend.users.show', $member) }}" class="btn btn-soft btn-sm btn-block">
                        <i class="fas fa-user"></i> Open account
                    </a>
                @endif
            </div>

            <div class="card card-pad pf-decide">
                <h3 class="card-title"><i class="fas fa-gavel"></i> Record a decision</h3>
                <p class="pf-decide-hint">Every option asks for confirmation, so a stray click cannot change a member's status.</p>
                <form method="POST" action="{{ route('backend.profiles.moderate', $profile) }}">
                    @csrf
                    <div class="field">
                        <label for="note">Note to the member</label>
                        <textarea name="note" id="note" class="input @error('note') error @enderror" rows="3" maxlength="500"
                                  placeholder="e.g. Please add a clear photo, or we removed the listed information because…">{{ old('note') }}</textarea>
                        <p class="text-tiny text-muted" style="margin:0">Sent with the decision, so the member knows what to change.</p>
                        @error('note')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="decision-grid" style="grid-template-columns:1fr">
                        <button name="decision" value="approved" class="btn btn-success"
                                data-confirm-title="Approve this profile?"
                                data-confirm="The profile becomes visible in search and the member can receive messages."
                                data-confirm-ok="Approve profile" data-confirm-icon="success"
                                data-confirm-color="#16A34A" data-confirm-focus-cancel>
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button name="decision" value="rejected" class="btn btn-danger-soft"
                                data-confirm-title="Reject this profile?"
                                data-confirm="The member will be notified and the profile stays hidden."
                                data-confirm-ok="Reject profile" data-confirm-icon="error"
                                data-confirm-color="#DC2626">
                            <i class="fas fa-xmark"></i> Reject
                        </button>
                        <button name="decision" value="suspended" class="btn btn-danger"
                                data-confirm-title="Suspend this account?"
                                data-confirm="The member is signed out and blocked from the site until you reactivate the account."
                                data-confirm-ok="Suspend account" data-confirm-icon="error"
                                data-confirm-color="#DC2626">
                            <i class="fas fa-ban"></i> Suspend Account
                        </button>
                        <button name="decision" value="pending" class="btn btn-outline"
                                data-confirm-title="Move back to pending?"
                                data-confirm="The profile leaves search until you review it again."
                                data-confirm-ok="Move to pending" data-confirm-icon="warning"
                                data-confirm-color="#D97706" data-confirm-focus-cancel>
                            <i class="fas fa-hourglass-half"></i> Back to Pending
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @unless ($openCase)
        <div class="alert alert-success pf-closed">
            <i class="fas fa-circle-check"></i>
            This profile is closed as {{ Str::lower($profile->statusLabel()) }}. A different decision can still be
            recorded here if something changes.
        </div>
    @endunless
@endsection
