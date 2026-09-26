@extends('frontend.layouts.auth')

@section('title', 'Create Free Profile')

@section('auth-content')
    <span class="auth-eyebrow"><i class="fas fa-gift"></i> Free forever · No hidden charges</span>
    <h1>Create your free profile</h1>
    <p class="auth-sub">Your account is 100% free. It takes about two minutes to get started.</p>

    <div class="auth-trust">
        <span><i class="fas fa-shield-halved"></i> Manually reviewed</span>
        <span><i class="fas fa-lock"></i> Private by default</span>
        <span><i class="fas fa-user-plus"></i> No payment ever</span>
    </div>

    <form method="POST" action="{{ route('register.store') }}">
        @csrf

        <div class="field">
            <label for="name">Full Name</label>
            <div class="input-icon-wrap">
                <i class="fas fa-user"></i>
                <input type="text" name="name" id="name" class="input @error('name') error @enderror"
                       value="{{ old('name') }}" placeholder="Your full name" required autofocus autocomplete="name">
            </div>
            @error('name') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        <div class="field">
            <label for="email">Email Address</label>
            <div class="input-icon-wrap">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="email" class="input @error('email') error @enderror"
                       value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="email">
            </div>
            @error('email') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        <div class="field">
            <label for="phone">Phone Number</label>
            <div class="input-icon-wrap">
                <i class="fas fa-phone"></i>
                <input type="tel" name="phone" id="phone" class="input @error('phone') error @enderror"
                       value="{{ old('phone') }}" placeholder="01XXXXXXXXX" required autocomplete="tel">
            </div>
            @error('phone') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        <div class="field">
            <label for="password">Password</label>
            <div class="input-icon-wrap">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" class="input @error('password') error @enderror"
                       placeholder="Min. 8 characters with letters & numbers" required autocomplete="new-password">
            </div>
            @error('password') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        <div class="field">
            <label for="password_confirmation">Confirm Password</label>
            <div class="input-icon-wrap">
                <i class="fas fa-lock"></i>
                <input type="password" name="password_confirmation" id="password_confirmation" class="input"
                       placeholder="Repeat your password" required autocomplete="new-password">
            </div>
        </div>

        <div class="field">
            <label class="checkbox">
                <input type="checkbox" name="terms" value="1" {{ old('terms') ? 'checked' : '' }}>
                I agree to the <a href="{{ route('pages.terms') }}" target="_blank">Terms & Conditions</a> and
                <a href="{{ route('pages.privacy') }}" target="_blank">Privacy Policy</a>.
            </label>
            @error('terms') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-block">
            <i class="fas fa-arrow-right"></i> Continue
        </button>
    </form>

    <div class="divider"></div>
    <p class="text-center text-muted text-small mb-4">Already have an account?</p>
    <a href="{{ route('login') }}" class="btn btn-brand-outline btn-block">Login Instead</a>
@endsection