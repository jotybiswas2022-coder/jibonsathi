@extends('frontend.layouts.app')

@section('title', 'Step 2 — Basic Information')

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

        <h1 style="font-size:24px;margin-bottom:4px">Tell us about yourself</h1>
        <p class="text-muted text-small mb-5" style="margin-bottom:22px">This helps us find members who genuinely complement your life.</p>

        <form method="POST" action="{{ route('register.step.store', ['step' => $step]) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Photo --}}
            <div class="section-block" style="background:var(--bg);border-style:dashed">
                <div class="sb-title"><i class="fas fa-camera"></i> Profile Photo <span class="sb-right text-small text-muted" style="font-weight:400">Optional</span></div>
                <p class="text-muted text-tiny mb-4">A clear, recent photo helps your profile stand out. JPG, PNG or WebP — max 4MB.</p>
                <input type="file" name="photo" id="photo" class="input" accept="image/jpeg,image/png,image/webp">
                @error('photo') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="field-group">
                <div class="field">
                    <label for="gender">I Am Looking As</label>
                    <select name="gender" id="gender" class="input @error('gender') error @enderror" required>
                        <option value="">Select</option>
                        @foreach (\App\Support\Reference::genders() as $key => $label)
                            <option value="{{ $key }}" @selected(old('gender', $profileUser->profile?->gender) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('gender') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label for="date_of_birth">Date Of Birth</label>
                    <input type="date" name="date_of_birth" id="date_of_birth" class="input @error('date_of_birth') error @enderror"
                           value="{{ old('date_of_birth', optional($profileUser->profile?->date_of_birth)->format('Y-m-d')) }}" required>
                    @error('date_of_birth') <span class="form-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="field-group">
                <div class="field">
                    <label for="height_cm">Height (cm)</label>
                    <input type="number" name="height_cm" id="height_cm" class="input @error('height_cm') error @enderror" min="120" max="220"
                           value="{{ old('height_cm', $profileUser->profile?->height_cm) }}" placeholder="e.g. 165" required>
                    @error('height_cm') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label for="marital_status">Marital Status</label>
                    <select name="marital_status" id="marital_status" class="input" required>
                        <option value="">Select</option>
                        @foreach (\App\Support\Reference::maritalStatuses() as $key => $label)
                            <option value="{{ $key }}" @selected(old('marital_status', $profileUser->profile?->marital_status) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="field-group">
                <div class="field">
                    <label for="religion">Religion</label>
                    <select name="religion" id="religion" class="input" required>
                        <option value="">Select</option>
                        @foreach (\App\Support\Reference::religions() as $key => $label)
                            <option value="{{ $key }}" @selected(old('religion', $profileUser->profile?->religion) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="mother_tongue">Mother Tongue</label>
                    <input type="text" name="mother_tongue" id="mother_tongue" class="input" maxlength="60"
                           value="{{ old('mother_tongue', $profileUser->profile?->mother_tongue) }}" placeholder="e.g. Bengali">
                </div>
            </div>

            <div class="field-group">
                <div class="field">
                    <label for="country">Country</label>
                    <select name="country" id="country" class="input" required>
                        <option value="">Select</option>
                        @foreach (\App\Support\Reference::countries() as $key => $label)
                            <option value="{{ $key }}" @selected(old('country', $profileUser->profile?->country) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="division">Division</label>
                    <select name="division" id="division" class="input" required>
                        <option value="">Select</option>
                        @foreach (\App\Support\Reference::divisionNames() as $division)
                            <option value="{{ $division }}" @selected(old('division', $profileUser->profile?->division) === $division)>{{ $division }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="field-group">
                <div class="field">
                    <label for="district">District</label>
                    <input type="text" name="district" id="district" class="input" maxlength="80" required
                           value="{{ old('district', $profileUser->profile?->district) }}" placeholder="e.g. Dhaka">
                </div>
                <div class="field">
                    <label for="city">City / Area <span class="text-muted">(optional)</span></label>
                    <input type="text" name="city" id="city" class="input" maxlength="120"
                           value="{{ old('city', $profileUser->profile?->city) }}" placeholder="e.g. Dhanmondi">
                </div>
            </div>

            <div class="field">
                <label for="headline">Profile Headline <span class="text-muted">(optional)</span></label>
                <input type="text" name="headline" id="headline" class="input" maxlength="160"
                       value="{{ old('headline', $profileUser->profile?->headline) }}" placeholder="One line that captures your personality">
            </div>

            <div class="field">
                <label for="about_me">About Me <span class="text-muted">(optional)</span></label>
                <textarea name="about_me" id="about_me" class="input" maxlength="1200" data-count="1200"
                          placeholder="A few warm sentences about who you are, your values and what you are looking for.">{{ old('about_me', $profileUser->profile?->about_me) }}</textarea>
                <span class="text-tiny text-muted" style="text-align:right" data-count-target></span>
            </div>

            <div class="flex justify-between mt-5" style="gap:12px">
                <form method="POST" action="{{ route('register.skip') }}">@csrf<button class="btn btn-ghost">Skip for now</button></form>
                <button type="submit" class="btn btn-primary btn-lg">Save & Continue <i class="fas fa-arrow-right"></i></button>
            </div>
        </form>
    </div>
</div>
@endsection