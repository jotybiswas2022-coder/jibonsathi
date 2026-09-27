@extends('frontend.layouts.auth')

@section('title', 'Login')

@section('auth-content')
    @php($siteName = \App\Models\SiteSetting::all_cached()['site_name'] ?? 'Jibon Sathi')
    {{-- A rejected email and a locked-out form are the whole page's problem, not one
         field's. They are raised as a validation error on "email", which used to land
         as a thin red line under a field and read as a typo. --}}
    @php($formError = $errors->first('email'))

    <span class="auth-eyebrow"><i class="fas fa-heart"></i> Member sign in</span>
    <h1>Welcome back</h1>
    <p class="auth-sub">Sign in to continue your search on {{ $siteName }}.</p>

    <form method="POST" action="{{ route('login.store') }}" novalidate>
        @csrf

        @if ($formError)
            <div class="alert alert-danger auth-alert" role="alert">
                <i class="fas fa-circle-exclamation"></i>
                <div>{{ $formError }}</div>
            </div>
        @endif

        <div class="field">
            <label for="email">Email address</label>
            <div class="input-icon-wrap">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="email" class="input @error('email') error @enderror"
                       value="{{ old('email') }}" placeholder="you@example.com" required autofocus
                       autocomplete="email" inputmode="email" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
            </div>
            {{-- Every "email" error is already in the alert above, so the field only
                 keeps its red border rather than saying the same thing twice. --}}
            @if (! $formError)
                <span class="field-hint">The email address you registered with.</span>
            @endif
        </div>

        <div class="field">
            <div class="field-label-row">
                <label for="password">Password</label>
                <a href="{{ route('password.request') }}" class="field-link">Forgot password?</a>
            </div>
            <div class="input-icon-wrap">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" class="input @error('password') error @enderror"
                       placeholder="Your password" required autocomplete="current-password">
                <button type="button" class="pass-toggle" data-password-toggle="password"
                        aria-label="Show password" aria-pressed="false" aria-controls="password">
                    <i class="fas fa-eye" aria-hidden="true"></i>
                </button>
            </div>
            @error('password')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <label class="checkbox auth-remember">
            <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
            Keep me signed in
        </label>

        <button type="submit" class="btn btn-primary btn-lg btn-block auth-submit">Sign in</button>

        {{-- Reassurance belongs after the decision, not above the first field where
             it pushed the form down the page. --}}
        <ul class="auth-reassure">
            <li><i class="fas fa-lock"></i> Your details stay private</li>
            <li><i class="fas fa-circle-check"></i> Verified members</li>
            <li><i class="fas fa-gift"></i> Free, always</li>
        </ul>
    </form>

    <div class="auth-alt">
        <span>New to {{ $siteName }}?</span>
        <a href="{{ route('register') }}" class="btn btn-brand-outline btn-block">Create your free profile</a>
    </div>
@endsection
