@extends('frontend.layouts.auth')

@section('title', 'Reset Password')

@section('auth-content')
    <h1>Choose a new password</h1>
    <p class="auth-sub">Make it strong — at least 8 characters with letters and numbers.</p>

    @if (session('errors'))
        <x-frontend::alert :tone="'danger'">
            {{ session('errors')->first('email') ?? 'Something went wrong. Please try again.' }}
        </x-frontend::alert>
    @endif

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="field">
            <label for="email">Email Address</label>
            <div class="input-icon-wrap">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="email" class="input @error('email') error @enderror"
                       value="{{ $email ?? old('email') }}" required autofocus autocomplete="email">
            </div>
            @error('email') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        <div class="field">
            <label for="password">New Password</label>
            <input type="password" name="password" id="password" class="input @error('password') error @enderror"
                   placeholder="Min. 8 characters" required autocomplete="new-password">
            @error('password') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        <div class="field">
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="input"
                   placeholder="Repeat new password" required autocomplete="new-password">
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-block">Reset Password</button>
    </form>
@endsection