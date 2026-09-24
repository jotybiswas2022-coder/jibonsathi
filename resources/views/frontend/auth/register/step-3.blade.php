@extends('frontend.layouts.app')

@section('title', 'Step 3 — Education & Career')

@section('content')
<div class="container" style="max-width:760px;padding-block:40px">
    <div class="card card-pad" style="padding:clamp(22px,4vw,40px)">
        <div class="flex items-center justify-between mb-4">
            <a href="{{ route('home') }}" class="brand">
                <span class="brand-mark"><i class="fas fa-heart"></i></span>
                Jora
            </a>
            <form method="POST" action="{{ route('register.skip') }}">@csrf<button class="btn btn-ghost btn-sm text-muted">Skip for now</button></form>
        </div>

        <x-frontend::wizard-progress :step="$step" :total="$total" />

        <h1 style="font-size:24px;margin-bottom:4px">Education & Career</h1>
        <p class="text-muted text-small mb-5" style="margin-bottom:22px">Members feel more confident connecting when they can see where you are in your journey.</p>

        <form method="POST" action="{{ route('register.step.store', ['step' => $step]) }}">
            @csrf
            @method('PUT')

            <div class="section-title"><i class="fas fa-graduation-cap"></i> Education</div>

            <div class="field">
                <label for="education_level">Highest Education</label>
                <select name="education_level" id="education_level" class="input" required>
                    <option value="">Select</option>
                    @foreach (\App\Support\Reference::educationLevels() as $key => $label)
                        <option value="{{ $key }}" @selected(old('education_level', $profileUser->education?->level) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('education_level') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="degree">Degree / Title</label>
                <input type="text" name="degree" id="degree" class="input" maxlength="120"
                       value="{{ old('degree', $profileUser->education?->degree) }}" placeholder="e.g. Bachelor of Business Administration">
            </div>

            <div class="field">
                <label for="field_of_study">Field of Study</label>
                <input type="text" name="field_of_study" id="field_of_study" class="input" maxlength="120"
                       value="{{ old('field_of_study', $profileUser->education?->field_of_study) }}" placeholder="e.g. Marketing">
            </div>

            <div class="field-group">
                <div class="field">
                    <label for="institution">Institution</label>
                    <input type="text" name="institution" id="institution" class="input" maxlength="160"
                           value="{{ old('institution', $profileUser->education?->institution) }}" placeholder="e.g. University of Dhaka">
                </div>
                <div class="field">
                    <label for="end_year">Passing Year</label>
                    <input type="number" name="end_year" id="end_year" class="input" min="1960" max="{{ now()->year }}" placeholder="e.g. 2022"
                           value="{{ old('end_year', $profileUser->education?->end_year) }}">
                </div>
            </div>

            <div class="section-title mt-5"><i class="fas fa-briefcase"></i> Career</div>

            <div class="field">
                <label for="profession">Profession</label>
                <input type="text" name="profession" id="profession" class="input" maxlength="120"
                       value="{{ old('profession', $profileUser->occupation?->designation) }}" placeholder="e.g. Software Engineer">
            </div>

            <div class="field">
                <label for="company">Company / Workplace</label>
                <input type="text" name="company" id="company" class="input" maxlength="160"
                       value="{{ old('company', $profileUser->occupation?->company) }}" placeholder="e.g. Tech Solutions Ltd.">
            </div>

            <div class="field">
                <label for="employment_type">Employment Type</label>
                <select name="employment_type" id="employment_type" class="input">
                    <option value="">Select</option>
                    @foreach (\App\Support\Reference::employmentTypes() as $key => $label)
                        <option value="{{ $key }}" @selected(old('employment_type', $profileUser->occupation?->employment_type) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field-group">
                <div class="field">
                    <label for="income_range">Annual Income</label>
                    <select name="income_range" id="income_range" class="input">
                        <option value="">Select</option>
                        @foreach (\App\Support\Reference::incomeRanges() as $key => $label)
                            <option value="{{ $key }}" @selected(old('income_range', $profileUser->occupation?->income_range) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="work_location">Work Location</label>
                    <input type="text" name="work_location" id="work_location" class="input" maxlength="120"
                           value="{{ old('work_location', $profileUser->occupation?->work_location) }}" placeholder="e.g. Dhaka">
                </div>
            </div>

            <div class="flex justify-between mt-5" style="gap:12px">
                <a href="{{ route('register.step', ['step' => 2]) }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back</a>
                <button type="submit" class="btn btn-primary btn-lg">Save & Continue <i class="fas fa-arrow-right"></i></button>
            </div>
        </form>
    </div>
</div>
@endsection