@extends('frontend.layouts.member')

@section('title', 'Profile Settings')

@section('member-content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Settings</h1>
            <p class="page-sub">Basic information about yourself.</p>
        </div>
    </div>

    @include('frontend.settings.partials.nav', ['active' => 'profile'])

    <div class="card card-pad" style="max-width:860px">
        <form method="POST" action="{{ route('settings.profile.update') }}">
            @csrf
            @method('PUT')

            <div class="field">
                <label for="name">Full Name</label>
                <input type="text" name="name" id="name" class="input @error('name') error @enderror" maxlength="120"
                       value="{{ old('name', $user->name) }}" required>
                @error('name') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="field-group">
                <div class="field">
                    <label for="gender">Gender</label>
                    <select name="gender" id="gender" class="input">
                        @foreach (\App\Support\Reference::genders() as $key => $label)
                            <option value="{{ $key }}" @selected(old('gender', $user->profile?->gender) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="date_of_birth">Date Of Birth</label>
                    <input type="date" name="date_of_birth" id="date_of_birth" class="input"
                           value="{{ old('date_of_birth', optional($user->profile?->date_of_birth)->format('Y-m-d')) }}">
                    @error('date_of_birth') <span class="form-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="field-group">
                <div class="field">
                    <label for="height_cm">Height (cm)</label>
                    <input type="number" name="height_cm" id="height_cm" class="input" min="120" max="220"
                           value="{{ old('height_cm', $user->profile?->height_cm) }}">
                </div>
                <div class="field">
                    <label for="marital_status">Marital Status</label>
                    <select name="marital_status" id="marital_status" class="input">
                        <option value="">Select</option>
                        @foreach (\App\Support\Reference::maritalStatuses() as $key => $label)
                            <option value="{{ $key }}" @selected(old('marital_status', $user->profile?->marital_status) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="field-group">
                <div class="field">
                    <label for="religion">Religion</label>
                    <select name="religion" id="religion" class="input">
                        <option value="">Select</option>
                        @foreach (\App\Support\Reference::religions() as $key => $label)
                            <option value="{{ $key }}" @selected(old('religion', $user->profile?->religion) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="mother_tongue">Mother Tongue</label>
                    <input type="text" name="mother_tongue" id="mother_tongue" class="input" maxlength="60"
                           value="{{ old('mother_tongue', $user->profile?->mother_tongue) }}">
                </div>
            </div>

            <div class="field-group">
                <div class="field">
                    <label for="country">Country</label>
                    <select name="country" id="country" class="input">
                        <option value="">Select</option>
                        @foreach (\App\Support\Reference::countries() as $key => $label)
                            <option value="{{ $key }}" @selected(old('country', $user->profile?->country) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="division">Division</label>
                    <select name="division" id="division" class="input">
                        <option value="">Select</option>
                        @foreach (\App\Support\Reference::divisionNames() as $division)
                            <option value="{{ $division }}" @selected(old('division', $user->profile?->division) === $division)>{{ $division }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="field-group">
                <div class="field">
                    <label for="district">District</label>
                    <input type="text" name="district" id="district" class="input" maxlength="80"
                           value="{{ old('district', $user->profile?->district) }}">
                </div>
                <div class="field">
                    <label for="city">City / Area</label>
                    <input type="text" name="city" id="city" class="input" maxlength="120"
                           value="{{ old('city', $user->profile?->city) }}">
                </div>
            </div>

            <div class="field">
                <label for="headline">Profile Headline</label>
                <input type="text" name="headline" id="headline" class="input" maxlength="160"
                       value="{{ old('headline', $user->profile?->headline) }}">
            </div>

            <div class="field">
                <label for="about_me">About Me</label>
                <textarea name="about_me" id="about_me" class="input" maxlength="1200" data-count="1200"
                          rows="5">{{ old('about_me', $user->profile?->about_me) }}</textarea>
                <span class="text-tiny text-muted" style="text-align:right" data-count-target></span>
            </div>

            <button class="btn btn-primary">Save Changes</button>
        </form>
    </div>
@endsection