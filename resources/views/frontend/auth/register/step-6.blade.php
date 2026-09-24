@extends('frontend.layouts.app')

@section('title', 'Step 6 — Partner Preference')

@section('content')
<div class="container" style="max-width:760px;padding-block:40px">
    <div class="card card-pad" style="padding:clamp(22px,4vw,40px)">
        <div class="flex items-center justify-between mb-4">
            <a href="{{ route('home') }}" class="brand">
                <span class="brand-mark"><i class="fas fa-heart"></i></span>
                Jibon Sathi
            </a>
            <form method="POST" action="{{ route('register.skip') }}">@csrf<button class="btn btn-ghost btn-sm text-muted">Skip for now</button></form>
        </div>

        <x-frontend::wizard-progress :step="$step" :total="$total" />

        <h1 style="font-size:24px;margin-bottom:4px">Your partner preference</h1>
        <p class="text-muted text-small mb-5" style="margin-bottom:22px">This is how we prioritise your recommendations. You can change any of this anytime.</p>

        <form method="POST" action="{{ route('register.step.store', ['step' => $step]) }}">
            @csrf
            @method('PUT')

            <div class="section-title"><i class="fas fa-bullseye"></i> Basics</div>

            <div class="field">
                <label for="preferred_gender">Seeking Profile Of</label>
                <select name="preferred_gender" id="preferred_gender" class="input" required>
                    <option value="">Select</option>
                    @foreach (\App\Support\Reference::genders() as $key => $label)
                        <option value="{{ $key }}" @selected(old('preferred_gender', $profileUser->partnerPreference?->preferred_gender) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('preferred_gender') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="field-group">
                <div class="field">
                    <label for="age_min">Age From</label>
                    <input type="number" name="age_min" id="age_min" class="input" min="18" max="80" placeholder="e.g. 23"
                           value="{{ old('age_min', $profileUser->partnerPreference?->age_min) }}" required>
                </div>
                <div class="field">
                    <label for="age_max">Age To</label>
                    <input type="number" name="age_max" id="age_max" class="input" min="18" max="80" placeholder="e.g. 30"
                           value="{{ old('age_max', $profileUser->partnerPreference?->age_max) }}" required>
                </div>
            </div>

            <div class="field-group">
                <div class="field">
                    <label for="height_min_cm">Height From (cm)</label>
                    <input type="number" name="height_min_cm" id="height_min_cm" class="input" min="120" max="220" placeholder="e.g. 152"
                           value="{{ old('height_min_cm', $profileUser->partnerPreference?->height_min_cm) }}">
                </div>
                <div class="field">
                    <label for="height_max_cm">Height To (cm)</label>
                    <input type="number" name="height_max_cm" id="height_max_cm" class="input" min="120" max="220" placeholder="e.g. 175"
                           value="{{ old('height_max_cm', $profileUser->partnerPreference?->height_max_cm) }}">
                </div>
            </div>

            <div class="section-title mt-4"><i class="fas fa-map-marker-alt"></i> Location</div>

            <div class="field-group">
                <div class="field">
                    <label for="preferred_country">Country</label>
                    <select name="preferred_country" id="preferred_country" class="input">
                        <option value="">Anywhere</option>
                        @foreach (\App\Support\Reference::countries() as $key => $label)
                            <option value="{{ $key }}" @selected(old('preferred_country', $profileUser->partnerPreference?->preferred_country) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="preferred_division">Division</label>
                    <select name="preferred_division" id="preferred_division" class="input">
                        <option value="">Anywhere</option>
                        @foreach (\App\Support\Reference::divisionNames() as $division)
                            <option value="{{ $division }}" @selected(old('preferred_division', $profileUser->partnerPreference?->preferred_division) === $division)>{{ $division }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="field">
                <label for="preferred_district">District</label>
                <input type="text" name="preferred_district" id="preferred_district" class="input" maxlength="80"
                       value="{{ old('preferred_district', $profileUser->partnerPreference?->preferred_district) }}" placeholder="e.g. Dhaka">
            </div>

            <div class="section-title mt-4"><i class="fas fa-sliders-h"></i> Education, Career & Lifestyle</div>

            <div class="field-group">
                <div class="field">
                    <label for="education_level">Minimum Education</label>
                    <select name="education_level" id="education_level" class="input">
                        <option value="">No Preference</option>
                        @foreach (\App\Support\Reference::educationLevels() as $key => $label)
                            <option value="{{ $key }}" @selected(old('education_level', $profileUser->partnerPreference?->education_level) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="profession">Profession</label>
                    <input type="text" name="profession" id="profession" class="input" maxlength="120"
                           value="{{ old('profession', $profileUser->partnerPreference?->profession) }}" placeholder="e.g. Doctor">
                </div>
            </div>

            <div class="field-group">
                <div class="field">
                    <label for="diet">Diet</label>
                    <select name="diet" id="diet" class="input">
                        <option value="">No Preference</option>
                        @foreach (\App\Support\Reference::diets() as $key => $label)
                            <option value="{{ $key }}" @selected(old('diet', $profileUser->partnerPreference?->diet) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="drinking">Alcohol</label>
                    <select name="drinking" id="drinking" class="input">
                        <option value="">No Preference</option>
                        @foreach (\App\Support\Reference::drinking() as $key => $label)
                            <option value="{{ $key }}" @selected(old('drinking', $profileUser->partnerPreference?->drinking) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="field">
                <label for="smoking">Smoking</label>
                <select name="smoking" id="smoking" class="input">
                    <option value="">No Preference</option>
                    @foreach (\App\Support\Reference::smoking() as $key => $label)
                        <option value="{{ $key }}" @selected(old('smoking', $profileUser->partnerPreference?->smoking) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="section-title mt-4"><i class="fas fa-church"></i> Religion & Marital Status <span class="text-muted text-tiny" style="font-weight:400">(optional)</span></div>

            <div class="field">
                <label for="religions_multi">Preferred Religions</label>
                <div class="chip-list">
                    @foreach (\App\Support\Reference::religions() as $key => $label)
                        @php($checked = in_array($key, old('religions', $profileUser->partnerPreference?->religions ?? []), true))
                        <label class="chip @if($checked) checked @endif">
                            <input type="checkbox" name="religions[]" value="{{ $key }}" @if($checked) checked @endif>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="field">
                <label for="marital_statuses_multi">Marital Status</label>
                <div class="chip-list">
                    @foreach (\App\Support\Reference::maritalStatuses() as $key => $label)
                        @php($checked = in_array($key, old('marital_statuses', $profileUser->partnerPreference?->marital_statuses ?? []), true))
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
                          placeholder="Anything else you would love in a partner.">{{ old('notes', $profileUser->partnerPreference?->notes) }}</textarea>
                <span class="text-tiny text-muted" style="text-align:right" data-count-target></span>
            </div>

            <div class="flex justify-between mt-5" style="gap:12px">
                <a href="{{ route('register.step', ['step' => 5]) }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back</a>
                <button type="submit" class="btn btn-primary btn-lg">Save & Continue <i class="fas fa-arrow-right"></i></button>
            </div>
        </form>
    </div>
</div>
@endsection