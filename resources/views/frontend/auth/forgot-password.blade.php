@extends('frontend.layouts.auth')

@section('title', 'Forgot Password')

@section('auth-content')
    <h1>Reset your password</h1>
    <p class="auth-sub">Enter your email and we'll send you a secure reset link.</p>

    @if (session('status'))
        <x-frontend::alert :tone="'success'">
            {{ session('status') }}
        </x-frontend::alert>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="field">
            <label for="email">Email Address</label>
            <div class="input-icon-wrap">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="email" class="input @error('email') error @enderror"
                       value="{{ old('email') }}" placeholder="you@example.com" required autofocus autocomplete="email">
            </div>
            @error('email') <span class="form-error">{{ $message }}</span> @enderror
        </div>
        <button type="submit" class="btn btn-primary btn-lg btn-block">Send Reset Link</button>
    </form>

    <div class="divider"></div>
    <p class="text-center text-muted text-small mb-4">Remembered it after all?</p>
    <a href="{{ route('login') }}" class="btn btn-brand-outline btn-block">Back to Login</a>
@endsection