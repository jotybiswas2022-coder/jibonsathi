@extends('frontend.layouts.app')

@section('title', 'Step 4 — Family Details')

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

        <h1 style="font-size:24px;margin-bottom:4px">About your family</h1>
        <p class="text-muted text-small mb-5" style="margin-bottom:22px">Nothing too personal — just what most families like to know before connecting.</p>

        <form method="POST" action="{{ route('register.step.store', ['step' => $step]) }}">
            @csrf
            @method('PUT')

            <div class="field-group">
                <div class="field">
                    <label for="family_type">Family Type</label>
                    <select name="family_type" id="family_type" class="input">
                        <option value="">Select</option>
                        @foreach (\App\Support\Reference::familyTypes() as $key => $label)
                            <option value="{{ $key }}" @selected(old('family_type', $profileUser->familyDetail?->family_type) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="family_status">Family Status</label>
                    <select name="family_status" id="family_status" class="input">
                        <option value="">Select</option>
                        @foreach (\App\Support\Reference::familyStatuses() as $key => $label)
                            <option value="{{ $key }}" @selected(old('family_status', $profileUser->familyDetail?->family_status) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="field">
                <label for="father_occupation">Father's Occupation</label>
                <input type="text" name="father_occupation" id="father_occupation" class="input" maxlength="120"
                       value="{{ old('father_occupation', $profileUser->familyDetail?->father_occupation) }}" placeholder="e.g. Retired Government Officer">
            </div>

            <div class="field">
                <label for="mother_occupation">Mother's Occupation</label>
                <input type="text" name="mother_occupation" id="mother_occupation" class="input" maxlength="120"
                       value="{{ old('mother_occupation', $profileUser->familyDetail?->mother_occupation) }}" placeholder="e.g. Homemaker">
            </div>

            <div class="field-group">
                <div class="field">
                    <label for="brothers">Brothers</label>
                    <input type="number" name="brothers" id="brothers" class="input" min="0" max="10" value="{{ old('brothers', $profileUser->familyDetail?->brothers ?? 0) }}">
                </div>
                <div class="field">
                    <label for="sisters">Sisters</label>
                    <input type="number" name="sisters" id="sisters" class="input" min="0" max="10" value="{{ old('sisters', $profileUser->familyDetail?->sisters ?? 0) }}">
                </div>
            </div>

            <div class="field">
                <label for="family_income_range">Family Income Range</label>
                <select name="family_income_range" id="family_income_range" class="input">
                    <option value="">Select</option>
                    @foreach (\App\Support\Reference::incomeRanges() as $key => $label)
                        <option value="{{ $key }}" @selected(old('family_income_range', $profileUser->familyDetail?->family_income_range) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="about_family">About My Family</label>
                <textarea name="about_family" id="about_family" class="input" maxlength="1000" data-count="1000"
                          placeholder="A short note about your family values, size and close relatives.">{{ old('about_family', $profileUser->familyDetail?->about_family) }}</textarea>
                <span class="text-tiny text-muted" style="text-align:right" data-count-target></span>
            </div>

            <div class="flex justify-between mt-5" style="gap:12px">
                <a href="{{ route('register.step', ['step' => 3]) }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back</a>
                <button type="submit" class="btn btn-primary btn-lg">Save & Continue <i class="fas fa-arrow-right"></i></button>
            </div>
        </form>
    </div>
</div>
@endsection