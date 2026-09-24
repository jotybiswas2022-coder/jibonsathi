@extends('frontend.layouts.member')

@section('title', 'Family Settings')

@section('member-content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Settings</h1>
            <p class="page-sub">Family details.</p>
        </div>
    </div>

    @include('frontend.settings.partials.nav', ['active' => 'family'])

    <div class="card card-pad" style="max-width:860px">
        <form method="POST" action="{{ route('settings.family.update') }}">
            @csrf
            @method('PUT')

            <div class="field-group">
                <div class="field">
                    <label for="family_type">Family Type</label>
                    <select name="family_type" id="family_type" class="input">
                        <option value="">Select</option>
                        @foreach (\App\Support\Reference::familyTypes() as $key => $label)
                            <option value="{{ $key }}" @selected(old('family_type', $user->familyDetail?->family_type) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="family_status">Family Status</label>
                    <select name="family_status" id="family_status" class="input">
                        <option value="">Select</option>
                        @foreach (\App\Support\Reference::familyStatuses() as $key => $label)
                            <option value="{{ $key }}" @selected(old('family_status', $user->familyDetail?->family_status) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="field">
                <label for="father_occupation">Father's Occupation</label>
                <input type="text" name="father_occupation" id="father_occupation" class="input" maxlength="120"
                       value="{{ old('father_occupation', $user->familyDetail?->father_occupation) }}">
            </div>

            <div class="field">
                <label for="mother_occupation">Mother's Occupation</label>
                <input type="text" name="mother_occupation" id="mother_occupation" class="input" maxlength="120"
                       value="{{ old('mother_occupation', $user->familyDetail?->mother_occupation) }}">
            </div>

            <div class="field-group">
                <div class="field">
                    <label for="brothers">Brothers</label>
                    <input type="number" name="brothers" id="brothers" class="input" min="0" max="10"
                           value="{{ old('brothers', $user->familyDetail?->brothers ?? 0) }}">
                </div>
                <div class="field">
                    <label for="sisters">Sisters</label>
                    <input type="number" name="sisters" id="sisters" class="input" min="0" max="10"
                           value="{{ old('sisters', $user->familyDetail?->sisters ?? 0) }}">
                </div>
            </div>

            <div class="field">
                <label for="family_income_range">Family Income Range</label>
                <select name="family_income_range" id="family_income_range" class="input">
                    <option value="">Select</option>
                    @foreach (\App\Support\Reference::incomeRanges() as $key => $label)
                        <option value="{{ $key }}" @selected(old('family_income_range', $user->familyDetail?->family_income_range) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="about_family">About My Family</label>
                <textarea name="about_family" id="about_family" class="input" maxlength="1000" data-count="1000"
                          rows="4">{{ old('about_family', $user->familyDetail?->about_family) }}</textarea>
                <span class="text-tiny text-muted" style="text-align:right" data-count-target></span>
            </div>

            <button class="btn btn-primary">Save Changes</button>
        </form>
    </div>
@endsection