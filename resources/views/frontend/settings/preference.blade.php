@extends('frontend.layouts.member')

@section('title', 'Partner Preference Settings')

@section('member-content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Settings</h1>
            <p class="page-sub">What you're looking for in a partner.</p>
        </div>
    </div>

    @include('frontend.settings.partials.nav', ['active' => 'preference'])

    <div class="card card-pad" style="max-width:860px">
        <form method="POST" action="{{ route('settings.preference.update') }}">
            @csrf
            @method('PUT')

            <div class="field">
                <label for="preferred_gender">Seeking Profile Of</label>
                <select name="preferred_gender" id="preferred_gender" class="input">
                    <option value="">Select</option>
                    @foreach (\App\Support\Reference::genders() as $key => $label)
                        <option value="{{ $key }}" @selected(old('preferred_gender', $user->partnerPreference?->preferred_gender) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('preferred_gender') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="field-group">
                <div class="field">
                    <label for="age_min">Age From</label>
                    <input type="number" name="age_min" id="age_min" class="input" min="18" max="80"
                           value="{{ old('age_min', $user->partnerPreference?->age_min) }}">
                </div>
                <div class="field">
                    <label for="age_max">Age To</label>
                    <input type="number" name="age_max" id="age_max" class="input" min="18" max="80"
                           value="{{ old('age_max', $user->partnerPreference?->age_max) }}">
                </div>
            </div>

            <div class="field-group">
                <div class="field">
                    <label for="height_min_cm">Height From (cm)</label>
                    <input type="number" name="height_min_cm" id="height_min_cm" class="input" min="120" max="220"
                           value="{{ old('height_min_cm', $user->partnerPreference?->height_min_cm) }}">
                </div>
                <div class="field">
                    <label for="height_max_cm">Height To (cm)</label>
                    <input type="number" name="height_max_cm" id="height_max_cm" class="input" min="120" max="220"
                           value="{{ old('height_max_cm', $user->partnerPreference?->height_max_cm) }}">
                </div>
            </div>

            <div class="field-group">
                <div class="field">
                    <label for="preferred_country">Country</label>
                    <select name="preferred_country" id="preferred_country" class="input">
                        <option value="">Anywhere</option>
                        @foreach (\App\Support\Reference::countries() as $key => $label)
                            <option value="{{ $key }}" @selected(old('preferred_country', $user->partnerPreference?->preferred_country) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="preferred_division">Division</label>
                    <select name="preferred_division" id="preferred_division" class="input">
                        <option value="">Anywhere</option>
                        @foreach (\App\Support\Reference::divisionNames() as $division)
                            <option value="{{ $division }}" @selected(old('preferred_division', $user->partnerPreference?->preferred_division) === $division)>{{ $division }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="field">
                <label for="preferred_district">District</label>
                <input type="text" name="preferred_district" id="preferred_district" class="input" maxlength="80"
                       value="{{ old('preferred_district', $user->partnerPreference?->preferred_district) }}">
            </div>

            <div class="field-group">
                <div class="field">
                    <label for="education_level">Minimum Education</label>
                    <select name="education_level" id="education_level" class="input">
                        <option value="">No Preference</option>
                        @foreach (\App\Support\Reference::educationLevels() as $key => $label)
                            <option value="{{ $key }}" @selected(old('education_level', $user->partnerPreference?->education_level) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="profession">Profession</label>
                    <input type="text" name="profession" id="profession" class="input" maxlength="120"
                           value="{{ old('profession', $user->partnerPreference?->profession) }}">
                </div>
            </div>

            <div class="field-group">
                <div class="field">
                    <label for="diet">Diet</label>
                    <select name="diet" id="diet" class="input">
                        <option value="">No Preference</option>
                        @foreach (\App\Support\Reference::diets() as $key => $label)
                            <option value="{{ $key }}" @selected(old('diet', $user->partnerPreference?->diet) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="drinking">Alcohol</label>
                    <select name="drinking" id="drinking" class="input">
                        <option value="">No Preference</option>
                        @foreach (\App\Support\Reference::drinking() as $key => $label)
                            <option value="{{ $key }}" @selected(old('drinking', $user->partnerPreference?->drinking) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="field">
                <label for="smoking">Smoking</label>
                <select name="smoking" id="smoking" class="input">
                    <option value="">No Preference</option>
                    @foreach (\App\Support\Reference::smoking() as $key => $label)
                        <option value="{{ $key }}" @selected(old('smoking', $user->partnerPreference?->smoking) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="section-title mt-4"><i class="fas fa-church"></i> Religion & Marital Status</div>

            <div class="field">
                <label>Preferred Religions</label>
                <div class="chip-list">
                    @foreach (\App\Support\Reference::religions() as $key => $label)
                        @php($checked = in_array($key, old('religions', $user->partnerPreference?->religions ?? []), true))
                        <label class="chip @if($checked) checked @endif">
                            <input type="checkbox" name="religions[]" value="{{ $key }}" @if($checked) checked @endif>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="field">
                <label>Marital Status</label>
                <div class="chip-list">
                    @foreach (\App\Support\Reference::maritalStatuses() as $key => $label)
                        @php($checked = in_array($key, old('marital_statuses', $user->partnerPreference?->marital_statuses ?? []), true))
                        <label class="chip @if($checked) checked @endif">
                            <input type="checkbox" name="marital_statuses[]" value="{{ $key }}" @if($checked) checked @endif>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="field mt-4">
                <label for="notes">Additional Notes</label>
                <textarea name="notes" id="notes" class="input" maxlength="800" data-count="800"
                          rows="4">{{ old('notes', $user->partnerPreference?->notes) }}</textarea>
                <span class="text-tiny text-muted" style="text-align:right" data-count-target></span>
            </div>

            <button class="btn btn-primary">Save Changes</button>
        </form>
    </div>
@endsection