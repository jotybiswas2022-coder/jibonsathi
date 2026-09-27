@extends('frontend.layouts.member')

@section('title', 'Account Verification')

@php
    use App\Models\Verification;

    /* One place decides what a state looks like, so the three cards, the two
       action cards and the history can never disagree about the same word. */
    $verStates = [
        'verified' => ['label' => 'Verified', 'tone' => 'success', 'icon' => 'fa-circle-check'],
        'pending' => ['label' => 'In review', 'tone' => 'warning', 'icon' => 'fa-hourglass-half'],
        'rejected' => ['label' => 'Not approved', 'tone' => 'danger', 'icon' => 'fa-ban'],
        'unverified' => ['label' => 'Not verified', 'tone' => 'muted', 'icon' => 'fa-circle'],
    ];
    $verIcons = [
        'email' => 'fa-envelope',
        'phone' => 'fa-mobile-screen',
        'profile' => 'fa-id-card',
    ];
    $verDocTypes = ['national_id' => 'National ID (NID)', 'passport' => 'Passport', 'driving_license' => 'Driving Licence'];

    $verState = static fn (string $key) => $verStates[$key] ?? $verStates['unverified'];
    $verDone = collect($statuses)->filter(static fn ($item) => $item['status'] === 'verified')->count();
    $verTotal = count($statuses);
    $verPercent = $verTotal ? (int) round($verDone / $verTotal * 100) : 0;
@endphp

@section('member-content')
    <div class="page-head ver-head">
        <div class="page-head-text">
            <h1 class="page-title">Verification</h1>
            <p class="page-sub">Verified profiles get a badge and rank higher in search results.</p>
        </div>

        {{-- The one number that answers "is there anything left to do". --}}
        <div class="ver-progress">
            <div class="ver-progress-top">
                <span class="ver-progress-num">{{ $verDone }}<span class="of">/{{ $verTotal }}</span></span>
                <span class="ver-progress-label">{{ $verDone === $verTotal ? 'Everything verified' : 'Steps completed' }}</span>
            </div>
            <div class="ver-bar" role="img" aria-label="{{ $verDone }} of {{ $verTotal }} checks complete">
                <span style="width:{{ $verPercent }}%"></span>
            </div>
        </div>
    </div>

    @if (session('success'))
        <x-frontend::alert :tone="'success'">{{ session('success') }}</x-frontend::alert>
    @endif

    @if (session('debug_code'))
        <x-frontend::alert :tone="'info'">
            <strong>Local dev helper:</strong> your phone verification code is {{ session('debug_code') }}
        </x-frontend::alert>
    @endif

    {{-- Where each of the three checks stands, with the value the old tiles threw away. --}}
    <div class="ver-status">
        @foreach ($statuses as $key => $item)
            @php $state = $verState($item['status']); @endphp
            <div class="ver-status-item is-{{ $item['status'] }}">
                <span class="ver-status-ico"><i class="fas {{ $verIcons[$key] ?? 'fa-shield-halved' }}"></i></span>
                <span class="ver-status-body">
                    <span class="ver-status-label">{{ $item['label'] }}</span>
                    <span class="ver-status-value">{{ $item['value'] }}</span>
                </span>
                <x-frontend::badge :tone="$state['tone']" :icon="$state['icon']">{{ $state['label'] }}</x-frontend::badge>
            </div>
        @endforeach
    </div>

    <div class="ver-cards">
        {{-- Phone --}}
        <section class="card card-pad ver-card">
            <header class="ver-card-head">
                <span class="ver-card-ico"><i class="fas fa-mobile-screen"></i></span>
                <div>
                    <h2 class="ver-card-title">Phone number</h2>
                    <p class="ver-card-sub">
                        @if ($statuses['phone']['status'] === 'verified')
                            Confirmed by a one-time code.
                        @else
                            A one-time code proves this number is yours.
                        @endif
                    </p>
                </div>
                <x-frontend::badge :tone="$verState($statuses['phone']['status'])['tone']" :icon="$verState($statuses['phone']['status'])['icon']">{{ $verState($statuses['phone']['status'])['label'] }}</x-frontend::badge>
            </header>

            @if ($statuses['phone']['status'] === 'verified')
                <div class="ver-done">
                    <i class="fas fa-circle-check"></i>
                    <div>
                        <strong>{{ auth()->user()->phone }}</strong>
                        <span>Verified {{ auth()->user()->phone_verified_at?->translatedFormat('j M Y') }}</span>
                    </div>
                </div>
            @elseif (auth()->user()->phone)
                <div class="ver-step">
                    <span class="ver-step-num">1</span>
                    <div class="ver-step-body">
                        <div class="ver-step-text">
                            <strong>Request a code</strong>
                            <span>We will text {{ auth()->user()->phone }}.</span>
                        </div>
                        <form method="POST" action="{{ route('verification.send-phone') }}">
                            @csrf
                            <button class="btn btn-soft ver-btn"><i class="fas fa-paper-plane"></i> Send code</button>
                        </form>
                    </div>
                </div>

                <div class="ver-step">
                    <span class="ver-step-num">2</span>
                    <div class="ver-step-body">
                        <div class="ver-step-text">
                            <strong>Enter the 6-digit code</strong>
                            <span>It expires shortly, so send a fresh one if it does not arrive.</span>
                        </div>
                        <form method="POST" action="{{ route('verification.confirm-phone') }}" class="ver-code">
                            @csrf
                            <input type="text" name="code" class="input ver-code-input" inputmode="numeric" autocomplete="one-time-code"
                                   pattern="[0-9]{6}" maxlength="6" placeholder="000000" required aria-label="6 digit code">
                            <button class="btn btn-primary">Confirm</button>
                            @error('code') <span class="form-error">{{ $message }}</span> @enderror
                        </form>
                    </div>
                </div>
            @else
                <div class="ver-empty">
                    <i class="fas fa-mobile-screen-button"></i>
                    <div>
                        <strong>No phone number on your profile yet</strong>
                        <span>Add one first, then come back here to confirm it.</span>
                    </div>
                    <a href="{{ route('settings.profile') }}" class="btn btn-outline">Add a number</a>
                </div>
            @endif
        </section>

        {{-- Identity --}}
        <section class="card card-pad ver-card">
            <header class="ver-card-head">
                <span class="ver-card-ico"><i class="fas fa-id-card"></i></span>
                <div>
                    <h2 class="ver-card-title">Profile identity</h2>
                    <p class="ver-card-sub">A government ID, held privately and seen only by our moderation team.</p>
                </div>
                <x-frontend::badge :tone="$pending ? 'warning' : $verState($statuses['profile']['status'])['tone']" :icon="$pending ? 'fa-hourglass-half' : $verState($statuses['profile']['status'])['icon']">{{ $pending ? 'In review' : $verState($statuses['profile']['status'])['label'] }}</x-frontend::badge>
            </header>

            @if ($pending)
                <div class="ver-done is-pending">
                    <i class="fas fa-hourglass-half"></i>
                    <div>
                        <strong>Your document is with the moderation team</strong>
                        <span>You will get a notification as soon as it is decided, usually within 24 hours. You can send a new one if this one is rejected.</span>
                    </div>
                </div>
            @elseif ($statuses['profile']['status'] === 'verified')
                <div class="ver-done">
                    <i class="fas fa-circle-check"></i>
                    <div>
                        <strong>Your identity is confirmed</strong>
                        <span>The green badge beside your name is live everywhere on the site.</span>
                    </div>
                </div>
            @else
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

                    <ul class="ver-hint">
                        <li><i class="fas fa-image"></i> JPG, PNG, WEBP or PDF</li>
                        <li><i class="fas fa-weight-hanging"></i> Up to 5 MB</li>
                        <li><i class="fas fa-eye-slash"></i> Never shown on your profile</li>
                    </ul>

                    <button class="btn btn-primary btn-lg"><i class="fas fa-paper-plane"></i> Submit for review</button>
                </form>
            @endif
        </section>
    </div>

    {{-- The history the controller was already fetching. --}}
    @if ($requests->isNotEmpty())
        <section class="card card-pad ver-history">
            <h2 class="ver-history-title">Request history</h2>

            @foreach ($requests as $request)
                <div class="ver-row">
                    <span class="ver-row-ico"><i class="fas {{ $request->typeIcon() }}"></i></span>
                    <div class="ver-row-body">
                        <div class="ver-row-top">
                            <strong>{{ $request->typeLabel() }}</strong>
                            <x-frontend::badge :tone="$request->statusTone()" :icon="$request->statusIcon()">{{ $request->statusLabel() }}</x-frontend::badge>
                        </div>
                        <div class="ver-row-meta">
                            <span><i class="fas fa-clock"></i> {{ $request->created_at->translatedFormat('j M Y, g:i a') }}</span>
                            @if ($request->type === Verification::TYPE_PROFILE && $request->document_type)
                                <span><i class="fas fa-file-lines"></i> {{ $verDocTypes[$request->document_type] ?? ucfirst(str_replace('_', ' ', $request->document_type)) }}</span>
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
                <p class="text-tiny text-muted ver-history-foot">Showing your ten most recent requests.</p>
            @endif
        </section>
    @endif
@endsection
