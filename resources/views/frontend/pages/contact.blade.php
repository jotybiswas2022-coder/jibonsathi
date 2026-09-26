@extends('frontend.layouts.app')

@section('title', 'Contact Us')

@section('content')
<div class="page-hero">
    <div class="container">
        <span class="section-eyebrow"><i class="fas fa-headset"></i> Support</span>
        <h1>Contact Us</h1>
        <p>Questions, feedback or need a helping hand? We're here.</p>
    </div>
</div>

<div class="container table-page" style="max-width:860px;padding-block:40px">

    <div class="card card-pad" style="max-width:620px;margin:0 auto">
        <form method="POST" action="{{ route('pages.contact.submit') }}">
            @csrf

            <div class="field-group">
                <div class="field">
                    <label for="name">Your Name</label>
                    <input type="text" name="name" id="name" class="input @error('name') error @enderror" maxlength="80"
                           value="{{ old('name', auth()->user()?->name) }}" required>
                    @error('name') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" class="input @error('email') error @enderror" maxlength="120"
                           value="{{ old('email', auth()->user()?->email) }}" required>
                    @error('email') <span class="form-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="field">
                <label for="subject">Subject</label>
                <input type="text" name="subject" id="subject" class="input" maxlength="120"
                       value="{{ old('subject') }}" placeholder="How can we help?">
            </div>

            <div class="field">
                <label for="message">Message</label>
                <textarea name="message" id="message" class="input @error('message') error @enderror" maxlength="2000" data-count="2000"
                          rows="6" required>{{ old('message') }}</textarea>
                <span class="text-tiny text-muted" style="text-align:right" data-count-target></span>
                @error('message') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <button class="btn btn-primary btn-lg btn-block"><i class="fas fa-paper-plane"></i> Send Message</button>
        </form>
    </div>

    @php($support = \App\Models\SiteSetting::get('support_email'))
    @if ($support)
        <p class="text-center text-muted text-small mt-4">
            Prefer email? Write to us at <a href="mailto:{{ $support }}" style="color:var(--brand);font-weight:600">{{ $support }}</a>
        </p>
    @endif
</div>
@endsection