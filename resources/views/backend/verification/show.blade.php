@extends('backend.layouts.app')

@php
    use App\Models\Verification;
    use App\Support\Media;

    $member = $verification->user;
    $openCase = $verification->isPending();
    $ext = $verification->documentExtension();
@endphp

@section('title', 'Verification — '.($member?->name ?? 'Member'))
@section('crumb', 'Identity verification · Case review')

@section('content')
    <div class="page-head">
        <div class="page-head-text">
            <h2>Document review</h2>
            <p>Check the document against the profile it belongs to, then record the decision. The note is kept with the case and shown to the member.</p>
        </div>
        <div class="page-head-actions">
            <a href="{{ route('backend.verification.index', request()->query()) }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to queue
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" data-auto-close><i class="fas fa-circle-check"></i> {{ session('success') }}</div>
    @endif

    {{-- Two columns that collapse on a phone. This used to be an inline
         "1fr 340px", which the narrow screen could not honour. --}}
    <div class="vf-case">
        <div class="vf-case-main">
            <div class="card card-pad">
                <div class="vf-case-head">
                    <h3 class="vf-case-title">
                        <span class="vf-case-ico"><i class="fas {{ $verification->typeIcon() }}"></i></span>
                        {{ $verification->typeLabel() }}
                    </h3>
                    <span class="badge badge-{{ $verification->statusTone() }}" style="font-size:13px">
                        <i class="fas fa-circle"></i> {{ $verification->statusLabel() }}
                    </span>
                </div>

                <ul class="vf-case-meta">
                    <li><i class="fas fa-clock"></i> Sent {{ $verification->created_at?->diffForHumans() ?? 'unknown' }}</li>
                    <li><i class="fas fa-calendar"></i> {{ $verification->created_at?->format('d M Y, H:i') ?? '—' }}</li>
                    @if ($verification->document_type)
                        <li><i class="fas fa-tag"></i> {{ $verification->document_type }}</li>
                    @endif
                    <li>
                        <i class="fas fa-paperclip"></i>
                        {{ $verification->hasDocument() ? $ext.' file attached' : 'No file attached' }}
                    </li>
                    @if ($verification->reviewer)
                        <li><i class="fas fa-user-check"></i> {{ $verification->reviewed_at ? 'Reviewed' : 'Opened' }} by {{ $verification->reviewer->name }}</li>
                    @endif
                </ul>

                @if ($verification->hasDocument())
                    <div class="vf-doc-frame">
                        {{-- The frame is the reason this page exists, so it is the one
                             place allowed to be tall. A capped height keeps a long
                             page from pushing the decision panel off the screen. --}}
                        <iframe src="{{ route('backend.verification.document', $verification) }}#toolbar=0&view=FitH"
                                title="Identity document of {{ $member?->name ?? 'the member' }}"
                                loading="lazy"></iframe>
                    </div>
                    <div class="vf-doc-actions">
                        <a href="{{ route('backend.verification.document', $verification) }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">
                            <i class="fas fa-up-right-from-square"></i> Open full size
                        </a>
                        <span class="vf-doc-note">
                            <i class="fas fa-lock"></i>
                            Only admins with the manage permission can open this file.
                        </span>
                    </div>
                @else
                    <div class="empty-state vf-no-doc">
                        <i class="fas fa-file-circle-xmark"></i>
                        <h3>No document attached</h3>
                        <p>
                            @if ($verification->type === Verification::TYPE_EMAIL || $verification->type === Verification::TYPE_PHONE)
                                An {{ Str::lower($verification->typeShortLabel()) }} check is confirmed against the code sent
                                to the member, so there is no file to look at. Approve it if the code was returned.
                            @else
                                This member submitted a profile check without a file, so there is nothing to review.
                            @endif
                        </p>
                    </div>
                @endif

                @if ($verification->note)
                    <div class="vf-quote">
                        <i class="fas fa-quote-left"></i>
                        <div style="min-width:0">
                            <div class="vf-quote-label">What the member wrote</div>
                            <div class="vf-quote-body">{{ $verification->note }}</div>
                        </div>
                    </div>
                @endif

                @if ($verification->admin_note)
                    <div class="vf-note">
                        <div class="vf-quote-label">Previous admin note</div>
                        <div class="vf-note-box">{{ $verification->admin_note }}</div>
                    </div>
                @endif
            </div>

            {{-- The profile the document is checked against, so a mismatch between
                 the two is visible without opening a second page. --}}
            @if ($member)
                <div class="card card-pad">
                    <h3 class="card-title"><i class="fas fa-address-card"></i> Profile the document should match</h3>
                    <dl class="vf-facts">
                        <div>
                            <dt>Name</dt>
                            <dd>{{ $member->name }}</dd>
                        </div>
                        @if ($member->occupation?->title)
                            <div>
                                <dt>Occupation</dt>
                                <dd>{{ $member->occupation->title }}</dd>
                            </div>
                        @endif
                        @if ($member->education?->level)
                            <div>
                                <dt>Education</dt>
                                <dd>{{ $member->education->level }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt>Member since</dt>
                            <dd>{{ $member->created_at?->format('d M Y') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt>Email</dt>
                            <dd>{{ $member->email }}</dd>
                        </div>
                        @if ($member->phone)
                            <div>
                                <dt>Phone</dt>
                                <dd>{{ $member->phone }}</dd>
                            </div>
                        @endif
                    </dl>
                    <a href="{{ route('backend.users.show', $member) }}" class="btn btn-soft btn-sm">
                        <i class="fas fa-user"></i> Open full profile
                    </a>
                </div>
            @endif

            @if ($others->isNotEmpty())
                <div class="card card-pad">
                    <h3 class="card-title"><i class="fas fa-clock-rotate-left"></i> Earlier submissions from this member</h3>
                    <p class="hint" style="margin:0 0 6px">Someone who has been sent back more than once is worth a closer look before the decision is recorded.</p>
                    @foreach ($others as $o)
                        <div class="vf-history-row">
                            <i class="fas {{ $o->typeIcon() }}" style="color:var(--muted-2)"></i>
                            <div class="vf-history-main">
                                <div class="vf-history-val">
                                    <a href="{{ route('backend.verification.show', $o) }}">{{ $o->typeLabel() }}</a>
                                    <span class="badge badge-{{ $o->statusTone() }}">{{ $o->statusLabel() }}</span>
                                </div>
                                <div class="vf-history-sub">
                                    Sent {{ $o->created_at?->diffForHumans() }}
                                    @if ($o->admin_note) · {{ Str::limit($o->admin_note, 70) }} @endif
                                </div>
                            </div>
                            <a href="{{ route('backend.verification.show', $o) }}" class="st-act" title="Open submission" aria-label="Open the earlier {{ $o->typeShortLabel() }} submission">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="vf-case-side">
            <div class="card card-pad vf-subject">
                <span class="avatar vf-subject-avatar">
                    @if ($member?->primaryPhoto)
                        <img src="{{ $member->primaryPhoto->url() }}" alt="" onerror="this.style.display='none'">
                    @else
                        <span class="initials" style="font-size:26px">{{ $member?->initials ?? 'J' }}</span>
                    @endif
                </span>
                <h3 class="vf-subject-name">{{ $member?->name ?? 'Deleted user' }}</h3>
                <p class="text-muted text-small vf-subject-mail">{{ $member?->email ?? 'No longer on Jibon Sathi' }}</p>
                @if ($member)
                    <a href="{{ route('backend.users.show', $member) }}" class="btn btn-soft btn-sm btn-block">
                        <i class="fas fa-user"></i> View member
                    </a>
                @endif
            </div>

            <div class="card card-pad vf-decide">
                <h3 class="card-title"><i class="fas fa-gavel"></i> Record a decision</h3>
                <p class="vf-decide-hint">
                    A note is required to reject, so the member knows what to fix before they try again.
                </p>
                <form method="POST" action="{{ route('backend.verification.decide', $verification) }}">
                    @csrf
                    <div class="field">
                        <label for="note">Note to the member</label>
                        <textarea name="note" id="note" class="input @error('note') error @enderror" rows="3" maxlength="500"
                                  placeholder="What did you check, or what has to change?">{{ old('note', $verification->admin_note) }}</textarea>
                        <p class="text-tiny text-muted" style="margin:0">Saved with the case and shown to the next admin.</p>
                        @error('note')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="decision-grid" style="grid-template-columns:1fr">
                        <button name="decision" value="approved" class="btn btn-success"
                                data-confirm-title="Approve this verification?"
                                data-confirm="The member gets a verified badge and can use the features that need one."
                                data-confirm-ok="Approve verification" data-confirm-icon="success"
                                data-confirm-color="#16A34A" data-confirm-focus-cancel>
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button name="decision" value="rejected" class="btn btn-danger-soft"
                                data-confirm-title="Reject this verification?"
                                data-confirm="The member is told the document was not accepted and can submit a new one."
                                data-confirm-ok="Reject verification" data-confirm-icon="error"
                                data-confirm-color="#DC2626">
                            <i class="fas fa-xmark"></i> Reject
                        </button>
                        <button name="decision" value="pending" class="btn btn-outline"
                                data-confirm-title="Keep this verification pending?"
                                data-confirm="Nothing changes except the status stays in the review queue."
                                data-confirm-ok="Keep pending" data-confirm-icon="question"
                                data-confirm-color="#6B7280" data-confirm-focus-cancel>
                            <i class="fas fa-hourglass-half"></i> Keep as Pending
                        </button>
                    </div>
                </form>
            </div>

            <form method="POST" action="{{ route('backend.verification.destroy', $verification) }}"
                  data-confirm-title="Delete this verification record?"
                  data-confirm="The record and its uploaded document are removed permanently."
                  data-confirm-ok="Delete record" data-confirm-icon="error"
                  data-confirm-color="#DC2626" class="vf-danger">
                @csrf @method('DELETE')
                <button class="btn btn-ghost btn-sm btn-block"><i class="fas fa-trash"></i> Delete record</button>
            </form>
        </div>
    </div>

    @unless ($openCase)
        <div class="alert alert-success vf-closed">
            <i class="fas fa-circle-check"></i>
            This case is closed as {{ Str::lower($verification->statusLabel()) }}. A different decision can still be
            recorded here if something changes.
        </div>
    @endunless
@endsection
