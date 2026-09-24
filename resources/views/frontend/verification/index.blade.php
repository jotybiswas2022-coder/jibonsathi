@extends('frontend.layouts.member')

@section('title', 'Account Verification')

@section('member-content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Verification</h1>
            <p class="page-sub">Verified profiles get a badge and rank higher in search results.</p>
        </div>
    </div>

    @if (session('debug_code'))
        <x-frontend::alert :tone="'info'">
            <strong>Local dev helper:</strong> your phone verification code is {{ session('debug_code') }}
        </x-frontend::alert>
    @endif

    {{-- Status summary --}}
    <div class="stat-grid">
        @foreach ($statuses as $key => $item)
            <div class="stat-card">
                <span class="stat-ico"
                      style="background:{{ $item['status'] === 'verified' ? 'var(--brand-050)' : '#f1f5f9' }};color:{{ $item['status'] === 'verified' ? 'var(--brand)' : 'var(--muted-2)' }}">
                    <i class="fas fa-{{ $item['status'] === 'verified' ? 'check' : 'lock' }}"></i>
                </span>
                <span class="stat-num" style="font-size:16px">{{ ucfirst($item['status']) }}</span>
                <span class="stat-label">{{ $item['label'] }}</span>
            </div>
        @endforeach
    </div>

    <div class="card card-pad" style="margin-top:22px">
        <h3 class="card-title"><i class="fas fa-phone"></i> Verify your phone number</h3>
        <p class="text-muted text-small mb-4">We'll send a one-time 6-digit code to {{ auth()->user()->phone ?: 'your phone (add one in settings first)' }}.</p>

        @if ($statuses['phone']['status'] === 'verified')
            <x-frontend::badge :tone="'success'"><i class="fas fa-check"></i> Phone verified</x-frontend::badge>
        @elseif (auth()->user()->phone)
            <form method="POST" action="{{ route('verification.send-phone') }}" style="display:inline">
                @csrf
                <button class="btn btn-soft"><i class="fas fa-paper-plane"></i> Send Code</button>
            </form>
            <form method="POST" action="{{ route('verification.confirm-phone') }}" class="flex gap-2 mt-3" style="gap:10px;align-items:center;flex-wrap:wrap">
                @csrf
                <input type="text" name="code" class="input" pattern="[0-9]{6}" maxlength="6" placeholder="6-digit code" required
                       style="max-width:170px">
                <button class="btn btn-primary">Confirm Code</button>
                @error('code') <span class="form-error">{{ $message }}</span> @enderror
            </form>
        @else
            <p class="text-muted text-small">Please add a phone number in your <a href="{{ route('settings.profile') }}">profile settings</a> first.</p>
        @endif
    </div>

    <div class="card card-pad" style="margin-top:22px">
        <h3 class="card-title"><i class="fas fa-shield-halved"></i> Verify your identity</h3>
        <p class="text-muted text-small mb-4">
            Upload a clear photo of your government ID (NID, passport or driving licence). Your document is stored privately and
            only shared with our moderation team.
        </p>

        @if ($pending)
            <x-frontend::alert :tone="'info'">
                You have a pending identity review. You'll be notified once it's decided (usually within 24 hours).
            </x-frontend::alert>
        @else
            <form method="POST" action="{{ route('verification.submit-profile') }}" enctype="multipart/form-data">
                @csrf
                <div class="field-group">
                    <div class="field">
                        <label for="document_type">Document Type</label>
                        <select name="document_type" id="document_type" class="input" required>
                            <option value="">Select</option>
                            @foreach (['national_id' => 'National ID (NID)', 'passport' => 'Passport', 'driving_license' => 'Driving Licence'] as $key => $label)
                                <option value="{{ $key }}" @selected(old('document_type') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('document_type') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field">
                        <label for="document">Document Photo</label>
                        <input type="file" name="document" id="document" class="input" accept="image/jpeg,image/png,image/webp,application/pdf" required>
                        @error('document') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="field">
                    <label for="ver-note">Note <span class="text-muted">(optional)</span></label>
                    <input type="text" name="note" id="ver-note" class="input" maxlength="300"
                           value="{{ old('note') }}" placeholder="Anything you'd like to add?">
                    @error('note') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <button class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit for Review</button>
            </form>
        @endif
    </div>
@endsection