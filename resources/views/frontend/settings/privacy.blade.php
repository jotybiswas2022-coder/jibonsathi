@extends('frontend.layouts.member')

@section('title', 'Privacy Settings')

@section('member-content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Settings</h1>
            <p class="page-sub">Control who can see you and how they can reach you.</p>
        </div>
    </div>

    @include('frontend.settings.partials.nav', ['active' => 'privacy'])

    <div class="card card-pad" style="max-width:860px">
        <form method="POST" action="{{ route('settings.privacy.update') }}">
            @csrf
            @method('PUT')

            <div class="field">
                <label>Profile Visibility</label>
                <div class="radio-grid">
                    @foreach (['public' => 'Public', 'members' => 'Members Only', 'private' => 'Private'] as $key => $label)
                        <label class="seg-opt seg-card">
                            <input type="radio" name="profile_visibility" value="{{ $key }}"
                                   @checked(old('profile_visibility', $user->profile?->profile_visibility) === $key)>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('profile_visibility') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="switch-field">
                <label class="switch-row">
                    <input type="checkbox" name="show_phone" value="1" @checked(old('show_phone', $user->profile?->show_phone))>
                    <span class="switch"></span>
                    <span><strong>Show my phone number</strong><br><span class="text-tiny text-muted">Display your phone on your profile.</span></span>
                </label>
            </div>

            <div class="switch-field">
                <label class="switch-row">
                    <input type="checkbox" name="show_email" value="1" @checked(old('show_email', $user->profile?->show_email))>
                    <span class="switch"></span>
                    <span><strong>Show my email address</strong><br><span class="text-tiny text-muted">Display your email on your profile.</span></span>
                </label>
            </div>

            <div class="switch-field">
                <label class="switch-row">
                    <input type="checkbox" name="allow_messages" value="1" @checked(old('allow_messages', $user->profile?->allow_messages ?? true))>
                    <span class="switch"></span>
                    <span><strong>Accept messages</strong><br><span class="text-tiny text-muted">Allow connected members to message you.</span></span>
                </label>
            </div>

            <div class="switch-field">
                <label class="switch-row">
                    <input type="checkbox" name="allow_profile_views" value="1" @checked(old('allow_profile_views', $user->profile?->allow_profile_views ?? true))>
                    <span class="switch"></span>
                    <span><strong>Record profile views</strong><br><span class="text-tiny text-muted">Count and show who viewed your profile.</span></span>
                </label>
            </div>

            <div class="switch-field">
                <label class="switch-row">
                    <input type="checkbox" name="show_online_status" value="1" @checked(old('show_online_status', $user->profile?->show_online_status ?? true))>
                    <span class="switch"></span>
                    <span><strong>Show online status</strong><br><span class="text-tiny text-muted">Let members see when you're active.</span></span>
                </label>
            </div>

            <button class="btn btn-primary">Save Changes</button>
        </form>
        <p class="text-tiny text-muted mt-4" style="margin-top:18px">
            Note: your photo, name, basic details and match percentage are always visible to other members so they can recognise you.
        </p>
    </div>
@endsection