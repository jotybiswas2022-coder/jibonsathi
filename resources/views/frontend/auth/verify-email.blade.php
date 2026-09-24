@extends('frontend.layouts.auth')

@section('title', 'Verify Email')

@section('auth-content')
    <h1>Verify your email</h1>
    <p class="auth-sub">We've sent a verification link to your inbox. Please check your email to activate your account.</p>

    @if (session('status') == 'verification-link-sent')
        <x-frontend::alert :tone="'success'">
            A fresh verification link has been sent to your email address.
        </x-frontend::alert>
    @endif

    <div class="card card-pad text-center" style="padding:34px 24px">
        <div class="es-ico" style="width:76px;height:76px;margin:0 auto 18px;background:var(--brand-050);color:var(--brand);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:32px">
            <i class="fas fa-envelope-open-text"></i>
        </div>
        <p class="text-muted text-small mb-4">Didn't get the email? We can send you another one.</p>
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-brand-outline btn-block">Resend Verification Email</button>
        </form>
    </div>

    <div class="divider"></div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-ghost btn-block text-muted">Logout</button>
    </form>
@endsection