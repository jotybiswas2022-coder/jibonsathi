@extends('frontend.layouts.member')

@section('title', 'Education & Career Settings')

@section('member-content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Settings</h1>
            <p class="page-sub">Education and career information.</p>
        </div>
    </div>

    @include('frontend.settings.partials.nav', ['active' => 'career'])

    <div class="card card-pad" style="max-width:860px">
        <form method="POST" action="{{ route('settings.career.update') }}">
            @csrf
            @method('PUT')

            <div class="field">
                <label for="education_level">Highest Education</label>
                <select name="education_level" id="education_level" class="input">
                    <option value="">Select</option>
                    @foreach (\App\Support\Reference::educationLevels() as $key => $label)
                        <option value="{{ $key }}" @selected(old('education_level', $user->education?->level) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('education_level') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="degree">Degree / Title</label>
                <input type="text" name="degree" id="degree" class="input" maxlength="120"
                       value="{{ old('degree', $user->education?->degree) }}">
            </div>

            <div class="field-group">
                <div class="field">
                    <label for="field_of_study">Field of Study</label>
                    <input type="text" name="field_of_study" id="field_of_study" class="input" maxlength="120"
                           value="{{ old('field_of_study', $user->education?->field_of_study) }}">
                </div>
                <div class="field">
                    <label for="institution">Institution</label>
                    <input type="text" name="institution" id="institution" class="input" maxlength="160"
                           value="{{ old('institution', $user->education?->institution) }}">
                </div>
            </div>

            <div class="field">
                <label for="end_year">Passing Year</label>
                <input type="number" name="end_year" id="end_year" class="input" min="1960" max="{{ now()->year }}"
                       value="{{ old('end_year', $user->education?->end_year) }}">
            </div>

            <div class="section-title mt-4"><i class="fas fa-briefcase"></i> Career</div>

            <div class="field-group">
                <div class="field">
                    <label for="profession">Profession</label>
                    <input type="text" name="profession" id="profession" class="input" maxlength="120"
                           value="{{ old('profession', $user->occupation?->designation) }}">
                </div>
                <div class="field">
                    <label for="company">Company</label>
                    <input type="text" name="company" id="company" class="input" maxlength="160"
                           value="{{ old('company', $user->occupation?->company) }}">
                </div>
            </div>

            <div class="field-group">
                <div class="field">
                    <label for="employment_type">Employment Type</label>
                    <select name="employment_type" id="employment_type" class="input">
                        <option value="">Select</option>
                        @foreach (\App\Support\Reference::employmentTypes() as $key => $label)
                            <option value="{{ $key }}" @selected(old('employment_type', $user->occupation?->employment_type) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="income_range">Annual Income (BDT)</label>
                    <select name="income_range" id="income_range" class="input">
                        <option value="">Select</option>
                        @foreach (\App\Support\Reference::incomeRanges() as $key => $label)
                            <option value="{{ $key }}" @selected(old('income_range', $user->occupation?->income_range) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="field">
                <label for="work_location">Work Location</label>
                <input type="text" name="work_location" id="work_location" class="input" maxlength="120"
                       value="{{ old('work_location', $user->occupation?->work_location) }}">
            </div>

            <button class="btn btn-primary">Save Changes</button>
        </form>
    </div>
@endsection