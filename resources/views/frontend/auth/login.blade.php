@extends('frontend.layouts.auth')

@section('title', 'Login')

@section('auth-content')
    <span class="auth-eyebrow"><i class="fas fa-heart"></i> Member sign in</span>
    <h1>Welcome back</h1>
    <p class="auth-sub">Sign in to continue your journey on Jibon Sathi.</p>

    <div class="auth-trust">
        <span><i class="fas fa-lock"></i> Private by default</span>
        <span><i class="fas fa-circle-check"></i> Verified members</span>
        <span><i class="fas fa-gift"></i> 100% free</span>
    </div>

    <form method="POST" action="{{ route('login.store') }}">
        @csrf

        <div class="field">
            <label for="email">Email Address</label>
            <div class="input-icon-wrap">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="email" class="input @error('email') error @enderror"
                       value="{{ old('email') }}" placeholder="you@example.com" required autofocus autocomplete="email">
            </div>
            @error('email')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="field">
            <label for="password">Password</label>
            <div class="input-icon-wrap">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" class="input @error('password') error @enderror"
                       placeholder="••••••••" required autocomplete="current-password">
                <button type="button" class="btn-icon btn-icon-sm" data-password-toggle="password"
                        style="position:absolute;right:8px;top:50%;transform:translateY(-50%);border:0;background:none;color:var(--muted-2)">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            @error('password')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex justify-between items-center mb-4" style="gap:10px;flex-wrap:wrap">
            <label class="checkbox">
                <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                Remember me
            </label>
            <a href="{{ route('password.request') }}" class="text-small" style="color:var(--brand);font-weight:600">
                Forgot password?
            </a>
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-block">Login</button>
    </form>

    <div class="divider"></div>
    <p class="text-center text-muted text-small mb-4">New to Jibon Sathi?</p>
    <a href="{{ route('register') }}" class="btn btn-brand-outline btn-block">Create Your Free Profile</a>
@endsection