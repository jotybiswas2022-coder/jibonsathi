@extends('frontend.layouts.member')

@section('title', 'Identity Verification')

@php
    use App\Models\Verification;

    /* One check drives this page, so one place decides how a state looks and
       which of the three steps the member is standing on. */
    $verStates = [
        'verified' => ['label' => 'Verified', 'tone' => 'success', 'icon' => 'fa-circle-check', 'stage' => 3],
        'pending' => ['label' => 'In review', 'tone' => 'warning', 'icon' => 'fa-hourglass-half', 'stage' => 2],
        'rejected' => ['label' => 'Not approved', 'tone' => 'danger', 'icon' => 'fa-ban', 'stage' => 1],
        'unverified' => ['label' => 'Not verified', 'tone' => 'muted', 'icon' => 'fa-id-card', 'stage' => 1],
    ];
    $verState = $verStates[$identity['status']] ?? $verStates['unverified'];
    $verStage = $verState['stage'];

    $verSteps = [
        ['label' => 'Upload your ID', 'icon' => 'fa-cloud-arrow-up'],
        ['label' => 'Team review', 'icon' => 'fa-magnifying-glass'],
        ['label' => 'Verified', 'icon' => 'fa-certificate'],
    ];

    $verDocTypes = [
        'national_id' => 'National ID (NID)',
        'passport' => 'Passport',
        'driving_license' => 'Driving Licence',
    ];

    /* The newest request, so a rejection can say why instead of only that it
       happened. */
    $verLatest = $requests->first();
    $verReason = $identity['status'] === 'rejected' ? $verLatest?->admin_note : null;
@endphp

@section('member-content')
    <div class="page-head ver-head">
        <div class="page-head-text">
            <h1 class="page-title">Identity verification</h1>
            <p class="page-sub">One check. Upload a government ID and our moderation team confirms it &mdash; usually within 24 hours.</p>
        </div>
        <x-frontend::badge :tone="$verState['tone']" :icon="$verState['icon']">{{ $verState['label'] }}</x-frontend::badge>
    </div>

    @if (session('success'))
        <x-frontend::alert :tone="'success'">{{ session('success') }}</x-frontend::alert>
    @endif

    @if ($verReason)
        <x-frontend::alert :tone="'danger'" :title="'We could not approve that document'">{{ $verReason }} You can send a different one below.</x-frontend::alert>
    @endif

    {{-- Where this single check stands: three steps, the current one lit. --}}
    <ol class="ver-track" aria-label="Verification progress">
        @foreach ($verSteps as $index => $step)
            @php $n = $index + 1; @endphp
            <li class="ver-track-step {{ $n < $verStage ? 'is-done' : ($n === $verStage ? 'is-current' : '') }}"
                @if ($n === $verStage) aria-current="step" @endif>
                <span class="ver-track-dot">
                    @if ($n < $verStage)
                        <i class="fas fa-check" aria-hidden="true"></i>
                    @else
                        <i class="fas {{ $step['icon'] }}" aria-hidden="true"></i>
                    @endif
                </span>
                <span class="ver-track-label">{{ $step['label'] }}</span>
            </li>
        @endforeach
    </ol>

    <div class="ver-layout">
        {{-- The action, or the reason there is nothing to do. --}}
        <section class="card card-pad ver-card">
            @if ($pending)
                <div class="ver-state is-pending">
                    <span class="ver-state-ico"><i class="fas fa-hourglass-half"></i></span>
                    <div class="ver-state-body">
                        <h2 class="ver-state-title">Your document is with the moderation team</h2>
                        <p class="ver-state-text">
                            @if ($verLatest)
                                Sent {{ $verLatest->created_at->translatedFormat('j M Y, g:i a') }}.
                            @endif
                            You will get a notification as soon as it is decided. There is nothing else to do here in the meantime.
                        </p>
                    </div>
                </div>
            @elseif ($identity['status'] === 'verified')
                <div class="ver-state is-verified">
                    <span class="ver-state-ico"><i class="fas fa-certificate"></i></span>
                    <div class="ver-state-body">
                        <h2 class="ver-state-title">Your identity is confirmed</h2>
                        <p class="ver-state-text">
                            The green badge sits beside your name everywhere on the site, and your profile ranks higher in search.
                            Your document is not shown to anyone, including other members.
                        </p>
                    </div>
                </div>
            @else
                <div class="ver-state">
                    <span class="ver-state-ico"><i class="fas fa-id-card"></i></span>
                    <div class="ver-state-body">
                        <h2 class="ver-state-title">Send us one document</h2>
                        <p class="ver-state-text">
                            A clear photo of your national ID, passport or driving licence. It is stored privately and opened only by
                            our moderation team.
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('verification.submit-profile') }}" enctype="multipart/form-data" class="ver-form">
                    @csrf
                    <div class="field-group">
                        <div class="field">
                            <label for="document_type">Document type</label>
                            <select name="document_type" id="document_type" class="input @error('document_type') error @enderror" required>
                                <option value="">Select a document</option>
                                @foreach ($verDocTypes as $docKey => $docLabel)
                                    <option value="{{ $docKey }}" @selected(old('document_type') === $docKey)>{{ $docLabel }}</option>
                                @endforeach
                            </select>
                            @error('document_type') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="field">
                            <label for="document">Document</label>
                            <input type="file" name="document" id="document" class="input ver-file @error('document') error @enderror"
                                   accept="image/jpeg,image/png,image/webp,application/pdf" required>
                            @error('document') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="field">
                        <label for="ver-note">Note <span class="text-muted">(optional)</span></label>
                        <input type="text" name="note" id="ver-note" class="input @error('note') error @enderror" maxlength="300"
                               value="{{ old('note') }}" placeholder="Anything that helps us match the document to you">
                        @error('note') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <button class="btn btn-primary btn-lg"><i class="fas fa-paper-plane"></i> Submit for review</button>
                </form>
            @endif
        </section>

        {{-- Why it is worth doing, kept beside the form on a desktop and under it
             on a phone. --}}
        <aside class="ver-perks">
            <h2 class="ver-perks-title">What verification gets you</h2>
            <ul class="ver-perks-list">
                <li>
                    <i class="fas fa-certificate"></i>
                    <div>
                        <strong>A verified badge</strong>
                        <span>Shown on your profile and beside your name in search and chat.</span>
                    </div>
                </li>
                <li>
                    <i class="fas fa-arrow-up"></i>
                    <div>
                        <strong>Higher in search</strong>
                        <span>Verified profiles are ranked ahead of unverified ones.</span>
                    </div>
                </li>
                <li>
                    <i class="fas fa-handshake"></i>
                    <div>
                        <strong>More genuine replies</strong>
                        <span>Families take a verified profile seriously, so you hear back more.</span>
                    </div>
                </li>
            </ul>

            <ul class="ver-hint">
                <li><i class="fas fa-image"></i> JPG, PNG, WEBP or PDF</li>
                <li><i class="fas fa-weight-hanging"></i> Up to 5 MB</li>
                <li><i class="fas fa-eye-slash"></i> Never shown on your profile</li>
            </ul>
        </aside>
    </div>

    {{-- The log of what was sent, so a second attempt is not a blind one. --}}
    @if ($requests->isNotEmpty())
        <section class="card card-pad ver-history">
            <h2 class="ver-history-title">Submitted documents</h2>

            @foreach ($requests as $request)
                <div class="ver-row">
                    <span class="ver-row-ico"><i class="fas fa-file-shield"></i></span>
                    <div class="ver-row-body">
                        <div class="ver-row-top">
                            <strong>{{ $verDocTypes[$request->document_type] ?? 'Document' }}</strong>
                            <x-frontend::badge :tone="$request->statusTone()" :icon="$request->statusIcon()">{{ $request->statusLabel() }}</x-frontend::badge>
                        </div>
                        <div class="ver-row-meta">
                            <span><i class="fas fa-clock"></i> Sent {{ $request->created_at->translatedFormat('j M Y, g:i a') }}</span>
                            @if ($request->hasDocument())
                                <span><i class="fas fa-paperclip"></i> {{ $request->documentExtension() }} file on file</span>
                            @else
                                <span><i class="fas fa-triangle-exclamation"></i> No file was attached</span>
                            @endif
                            @if ($request->reviewed_at)
                                <span><i class="fas fa-gavel"></i> Decided {{ $request->reviewed_at->translatedFormat('j M Y') }}</span>
                            @endif
                        </div>
                        @if ($request->admin_note)
                            <p class="ver-row-note"><i class="fas fa-quote-left"></i> {{ $request->admin_note }}</p>
                        @endif
                    </div>
                </div>
            @endforeach

            @if ($requests->count() === 10)
                <p class="text-tiny text-muted ver-history-foot">Showing your ten most recent submissions.</p>
            @endif
        </section>
    @endif
@endsection
