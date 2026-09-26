@extends('backend.layouts.app')

@php
    use App\Support\Media;

    $member = $report->reportedUser;
    $openCases = $report->isOpen();
@endphp

@section('title', 'Report — '.($member?->name ?? 'User'))
@section('crumb', 'Reports · Case review')

@section('content')
    <div class="page-head">
        <div class="page-head-text">
            <h2>Case review</h2>
            <p>Read what the reporter said, check the member, then record the decision. Every action here is written to the case history.</p>
        </div>
        <div class="page-head-actions">
            <a href="{{ route('backend.reports.index', request()->query()) }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to queue
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" data-auto-close><i class="fas fa-circle-check"></i> {{ session('success') }}</div>
    @endif

    {{-- Two columns that collapse on a phone. This used to be an inline
         "1fr 320px", which the narrow screen could not honour. --}}
    <div class="rp-case">
        <div class="rp-case-main">
            <div class="card card-pad">
                <div class="rp-case-head">
                    <h3 class="rp-case-title">
                        <span class="rp-case-ico"><i class="fas fa-flag"></i></span>
                        {{ $report->reasonLabel() }}
                    </h3>
                    <span class="badge badge-{{ $report->statusTone() }}" style="font-size:13px">
                        <i class="fas fa-circle"></i> {{ $report->statusLabel() }}
                    </span>
                </div>

                <ul class="rp-case-meta">
                    <li><i class="fas fa-clock"></i> Filed {{ $report->created_at?->diffForHumans() ?? 'unknown' }}</li>
                    <li><i class="fas fa-calendar"></i> {{ $report->created_at?->format('d M Y, H:i') ?? '—' }}</li>
                    @if ($report->handler)
                        <li><i class="fas fa-user-check"></i> Handled by {{ $report->handler->name }}</li>
                    @endif
                    @if ($report->resolved_at)
                        <li><i class="fas fa-flag-checkered"></i> Closed {{ $report->resolved_at->diffForHumans() }}</li>
                    @endif
                    @if ($report->reportedUserReportTotal() > 1)
                        <li><i class="fas fa-repeat"></i> {{ $report->reportedUserReportTotal() }} reports on this member</li>
                    @endif
                </ul>

                @if ($report->description)
                    <div class="rp-quote">
                        <i class="fas fa-quote-left"></i>
                        <div style="min-width:0">
                            <div class="rp-quote-label">What {{ $report->reporter?->name ?? 'the reporter' }} said</div>
                            <div class="rp-quote-body">{{ $report->description }}</div>
                        </div>
                    </div>
                @else
                    <div class="rp-quote">
                        <i class="fas fa-circle-info"></i>
                        <div>
                            <div class="rp-quote-label">No description</div>
                            <div class="rp-quote-body">The reporter filed this without saying what happened.</div>
                        </div>
                    </div>
                @endif

                @if ($report->admin_note)
                    <div class="rp-note">
                        <div class="rp-quote-label">Admin note</div>
                        <div class="rp-note-box">{{ $report->admin_note }}</div>
                    </div>
                @endif
            </div>

            @if ($member?->photos?->isNotEmpty())
                <div class="card card-pad">
                    <h3 class="card-title"><i class="fas fa-images"></i> {{ $member->name }}&rsquo;s photos</h3>
                    <p class="hint" style="margin:0 0 12px">Everything on the profile, so the report can be judged against what other members actually see.</p>
                    <div class="rp-photos">
                        @foreach ($member->photos as $photo)
                            <a href="{{ $photo->url() }}" target="_blank" rel="noopener" aria-label="Open photo {{ $loop->iteration }} of {{ $member->name }} at full size">
                                <img src="{{ $photo->url() }}" alt="" loading="lazy">
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($history->isNotEmpty())
                <div class="card card-pad">
                    <h3 class="card-title"><i class="fas fa-clock-rotate-left"></i> Other reports on this member</h3>
                    <p class="hint" style="margin:0 0 6px">A member with a pattern is worth a closer look before a decision is recorded.</p>
                    @foreach ($history as $h)
                        <div class="rp-history-row">
                            <i class="fas fa-flag" style="color:var(--muted-2)"></i>
                            <div class="rp-history-main">
                                <div class="rp-history-val">
                                    <a href="{{ route('backend.reports.show', $h) }}">{{ $h->reasonLabel() }}</a>
                                    <span class="badge badge-{{ $h->statusTone() }}">{{ $h->statusLabel() }}</span>
                                </div>
                                <div class="rp-history-sub">By {{ $h->reporter?->name ?? 'Member' }} · {{ $h->created_at?->diffForHumans() }}</div>
                            </div>
                            <a href="{{ route('backend.reports.show', $h) }}" class="st-act" title="Open case" aria-label="Open the earlier case against {{ $member?->name ?? 'this member' }}">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="rp-case-side">
            <div class="card card-pad rp-subject">
                <span class="avatar rp-subject-avatar">
                    <img src="{{ $member?->primaryPhoto?->url() ?? Media::avatar($member?->name ?? 'Jibon Sathi') }}" alt="" onerror="this.style.display='none'">
                    <span class="initials" style="font-size:26px">{{ $member?->initials ?? 'J' }}</span>
                </span>
                <h3 class="rp-subject-name">{{ $member?->name ?? 'Deleted user' }}</h3>
                <p class="text-muted text-small rp-subject-mail">{{ $member?->email ?? 'No longer on Jibon Sathi' }}</p>
                @if ($member)
                    <a href="{{ route('backend.users.show', $member) }}" class="btn btn-soft btn-sm btn-block">
                        <i class="fas fa-user"></i> View member
                    </a>
                @endif
            </div>

            <div class="card card-pad rp-decide">
                <h3 class="card-title"><i class="fas fa-gavel"></i> Record a decision</h3>
                <p class="rp-decide-hint">The note is saved with the case and shown to the next admin who opens it.</p>
                <form method="POST" action="{{ route('backend.reports.decide', $report) }}">
                    @csrf
                    <div class="field">
                        <label for="note">Admin note</label>
                        <textarea name="note" id="note" class="input" rows="3" maxlength="500"
                                  placeholder="What you found and why you decided this…">{{ old('note') }}</textarea>
                    </div>
                    <div class="decision-grid" style="grid-template-columns:1fr">
                        <button name="decision" value="investigating" class="btn btn-warning-soft"
                                data-confirm-title="Mark as investigating?"
                                data-confirm="The case stays open and the reported member is not notified."
                                data-confirm-ok="Mark investigating" data-confirm-icon="warning"
                                data-confirm-color="#D97706" data-confirm-focus-cancel>
                            <i class="fas fa-magnifying-glass"></i> Mark Investigating
                        </button>
                        <button name="decision" value="resolved" class="btn btn-success"
                                data-confirm-title="Resolve this case?"
                                data-confirm="The case is closed and the reporter is notified."
                                data-confirm-ok="Resolve case" data-confirm-icon="success"
                                data-confirm-color="#16A34A" data-confirm-focus-cancel>
                            <i class="fas fa-check"></i> Resolve
                        </button>
                        <button name="decision" value="dismissed" class="btn btn-outline"
                                data-confirm-title="Dismiss this case?"
                                data-confirm="The report is closed with no action taken."
                                data-confirm-ok="Dismiss case" data-confirm-icon="question"
                                data-confirm-color="#6B7280" data-confirm-focus-cancel>
                            <i class="fas fa-xmark"></i> Dismiss
                        </button>
                        <button name="decision" value="suspend" class="btn btn-danger"
                                data-confirm-title="Suspend the reported member?"
                                data-confirm="{{ $member?->name ?? 'The member' }} is signed out and blocked from the site."
                                data-confirm-ok="Suspend member" data-confirm-icon="error"
                                data-confirm-color="#DC2626">
                            <i class="fas fa-ban"></i> Suspend Reported Member
                        </button>
                    </div>
                </form>
            </div>

            @unless ($openCases)
                <div class="alert alert-success" style="margin:0">
                    <i class="fas fa-circle-check"></i>
                    This case is closed as {{ Str::lower($report->statusLabel()) }}. A different decision can still be
                    recorded here if something changes.
                </div>
            @endunless
        </div>
    </div>
@endsection
