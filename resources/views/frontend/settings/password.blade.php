@extends('frontend.layouts.member')

@section('title', 'Change Password')

@section('member-content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Settings</h1>
            <p class="page-sub">Update your password.</p>
        </div>
    </div>

    @include('frontend.settings.partials.nav', ['active' => 'password'])

    <div class="card card-pad" style="max-width:640px">
        <form method="POST" action="{{ route('settings.password.update') }}">
            @csrf
            @method('PUT')

            <div class="field">
                <label for="current_password">Current Password</label>
                <input type="password" name="current_password" id="current_password" class="input @error('current_password') error @enderror"
                       required autocomplete="current-password">
                @error('current_password') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="password">New Password</label>
                <input type="password" name="password" id="password" class="input @error('password') error @enderror"
                       placeholder="Min. 8 characters with letters & numbers" required autocomplete="new-password">
                @error('password') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">Confirm New Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="input"
                       required autocomplete="new-password">
            </div>

            <button class="btn btn-primary">Update Password</button>
        </form>
    </div>
@endsection