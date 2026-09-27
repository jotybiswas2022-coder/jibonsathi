@extends('frontend.layouts.app')

@section('title', 'Step 2 — Basic Information')

@php
    use App\Services\ProfileService;
    use App\Support\Reference;

    $profile = $profileUser->profile;
    $steps = ProfileService::STEPS;
    $completion = auth()->user()->completion();

    // The country decides whether the member picks a division or types a state.
    $country = old('country', $profile?->country);
    $storedDivision = old('division', $profile?->division);
    $usesDivisions = Reference::usesDivisions($country);
@endphp

@section('content')
<div class="reg-wrap">
    <div class="reg-shell">
        {{-- Step rail: the whole journey, so the member knows how much is left --}}
        <aside class="reg-rail">
            <div class="reg-rail-card">
                <a href="{{ route('home') }}" class="brand reg-brand">
                    <span class="brand-mark"><i class="fas fa-heart"></i></span>
                    Jibon Sathi
                </a>

                <div class="reg-progress-head">
                    <span>Profile completion</span>
                    <strong>{{ $completion }}%</strong>
                </div>
                <div class="reg-progress-bar"><span style="width: {{ $completion }}%"></span></div>

                <ol class="reg-steps">
                    @foreach ($steps as $number => $label)
                        <li class="reg-step {{ $number < $step ? 'is-done' : ($number == $step ? 'is-current' : '') }}">
                            <span class="reg-step-num">
                                @if ($number < $step)
                                    <i class="fas fa-check"></i>
                                @else
                                    {{ $number }}
                                @endif
                            </span>
                            <span class="reg-step-label">{{ $label }}</span>
                        </li>
                    @endforeach
                </ol>

                <div class="reg-rail-note">
                    <i class="fas fa-circle-info"></i>
                    <span>Your profile is private until you finish. You can come back to any step later.</span>
                </div>

                <form method="POST" action="{{ route('register.skip') }}" class="reg-skip">
                    @csrf
                    <button class="btn btn-ghost btn-block text-muted">Skip for now</button>
                </form>
            </div>
        </aside>

        <div class="reg-panel">
            <div class="card reg-card">
                <div class="reg-head">
                    <span class="reg-eyebrow">
                        <i class="fas fa-user"></i>
                        Step {{ $step }} of {{ $total }} · {{ $steps[$step] ?? '' }}
                    </span>
                    <h1>Tell us about yourself</h1>
                    <p class="text-muted">This helps us find members who genuinely complement your life.</p>
                </div>

                <form method="POST" action="{{ route('register.step.store', ['step' => $step]) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- Photo. Uses the shared uploader: dropping or picking a file
                         previews it and checks the size and dimensions before the
                         step is saved. --}}
                    <div class="reg-photo">
                        <div class="reg-photo-head">
                            <span class="reg-photo-ico"><i class="fas fa-camera"></i></span>
                            <div>
                                <h2>Profile photo <span class="text-muted" style="font-weight:400">· optional</span></h2>
                                <p>A clear, recent photo helps your profile stand out.</p>
                            </div>
                        </div>

                        <label class="pm-drop" data-photo-drop>
                            <input type="file" name="photo" id="photo" class="pm-file"
                                   accept="image/jpeg,image/png,image/webp">
                            <span class="pm-drop-ico"><i class="fas fa-cloud-arrow-up"></i></span>
                            <span class="pm-drop-title">Choose a photo to upload</span>
                            <span class="pm-drop-sub">or drag and drop one here</span>
                        </label>

                        <div class="pm-preview" data-photo-preview hidden>
                            <img src="" alt="" class="pm-preview-img" data-photo-img>
                            <div class="pm-preview-body">
                                <strong class="pm-preview-name" data-photo-name></strong>
                                <span class="pm-preview-note" data-photo-note></span>
                            </div>
                            <button type="button" class="pm-act pm-act-plain" data-photo-clear
                                    title="Choose a different photo" aria-label="Choose a different photo">
                                <i class="fas fa-xmark"></i>
                            </button>
                        </div>

                        <p class="pm-upload-note reg-photo-note">
                            <i class="fas fa-image"></i> JPG, PNG or WebP &middot; up to 4 MB &middot; at least 200&times;200
                        </p>

                        @error('photo')
                            <div class="alert alert-danger pm-alert"><i class="fas fa-circle-exclamation"></i><span>{{ $message }}</span></div>
                        @enderror
                    </div>

                    {{-- Identity --}}
                    <div class="section-title"><i class="fas fa-id-card"></i> The basics</div>

                    <div class="field-group">
                        <div class="field">
                            <label for="gender">I Am Looking As</label>
                            <select name="gender" id="gender" class="input @error('gender') error @enderror" required>
                                <option value="">Select</option>
                                @foreach (Reference::genders() as $key => $label)
                                    <option value="{{ $key }}" @selected(old('gender', $profile?->gender) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('gender') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="field">
                            <label for="date_of_birth">Date Of Birth</label>
                            <input type="date" name="date_of_birth" id="date_of_birth" class="input @error('date_of_birth') error @enderror"
                                   value="{{ old('date_of_birth', optional($profile?->date_of_birth)->format('Y-m-d')) }}" required>
                            @error('date_of_birth') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="field-group">
                        <div class="field">
                            <label for="height_cm">Height (cm)</label>
                            <input type="number" name="height_cm" id="height_cm" class="input @error('height_cm') error @enderror" min="120" max="220"
                                   value="{{ old('height_cm', $profile?->height_cm) }}" placeholder="e.g. 165" required>
                            @error('height_cm') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="field">
                            <label for="marital_status">Marital Status</label>
                            <select name="marital_status" id="marital_status" class="input @error('marital_status') error @enderror" required>
                                <option value="">Select</option>
                                @foreach (Reference::maritalStatuses() as $key => $label)
                                    <option value="{{ $key }}" @selected(old('marital_status', $profile?->marital_status) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('marital_status') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="field-group">
                        <div class="field">
                            <label for="religion">Religion</label>
                            <select name="religion" id="religion" class="input @error('religion') error @enderror" required>
                                <option value="">Select</option>
                                @foreach (Reference::religions() as $key => $label)
                                    <option value="{{ $key }}" @selected(old('religion', $profile?->religion) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('religion') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="field">
                            <label for="mother_tongue">Mother Tongue</label>
                            <input type="text" name="mother_tongue" id="mother_tongue" class="input" maxlength="60"
                                   value="{{ old('mother_tongue', $profile?->mother_tongue) }}" placeholder="e.g. Bengali">
                        </div>
                    </div>

                    {{-- Location --}}
                    <div class="section-title mt-5"><i class="fas fa-location-dot"></i> Where you live</div>

                    <div class="field-group">
                        <div class="field">
                            <label for="country">Country</label>
                            <select name="country" id="country" class="input @error('country') error @enderror"
                                    data-country-select required>
                                <option value="">Select</option>
                                @foreach (Reference::countries() as $key => $label)
                                    <option value="{{ $key }}" @selected($country === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('country') <span class="form-error">{{ $message }}</span> @enderror
                        </div>

                        {{-- Bangladesh answers with a division; anywhere else types a state.
                             Both controls exist and the script shows the right one, so the
                             page still works if JavaScript never runs. --}}
                        <div class="field">
                            <label for="division" data-division-label>{{ $usesDivisions ? 'Division' : 'State / Province / Region' }}</label>

                            <select name="division" id="division" class="input @error('division') error @enderror"
                                    data-division-select @if (! $usesDivisions) hidden disabled @endif>
                                <option value="">Select</option>
                                @foreach (Reference::divisionNames() as $division)
                                    <option value="{{ $division }}" @selected($storedDivision === $division)>{{ $division }}</option>
                                @endforeach
                            </select>

                            <input type="text" name="division" id="division_state" class="input @error('division') error @enderror"
                                   maxlength="80" placeholder="State, province or region"
                                   value="{{ $storedDivision }}" data-division-text
                                   @if ($usesDivisions) hidden disabled @endif>

                            <span class="field-hint" data-division-hint @if ($usesDivisions) hidden @endif>
                                State, province or region outside Bangladesh.
                            </span>
                            @error('division') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="field-group">
                        <div class="field">
                            <label for="district">District</label>
                            <input type="text" name="district" id="district" class="input @error('district') error @enderror" maxlength="80" required
                                   value="{{ old('district', $profile?->district) }}" placeholder="e.g. Dhaka">
                            @error('district') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="field">
                            <label for="city">City / Area <span class="text-muted">(optional)</span></label>
                            <input type="text" name="city" id="city" class="input" maxlength="120"
                                   value="{{ old('city', $profile?->city) }}" placeholder="e.g. Dhanmondi">
                        </div>
                    </div>

                    {{-- About --}}
                    <div class="section-title mt-5"><i class="fas fa-quote-left"></i> In your words</div>

                    <div class="field">
                        <label for="headline">Profile Headline <span class="text-muted">(optional)</span></label>
                        <input type="text" name="headline" id="headline" class="input" maxlength="160"
                               value="{{ old('headline', $profile?->headline) }}" placeholder="One line that captures your personality">
                    </div>

                    <div class="field">
                        <label for="about_me">About Me <span class="text-muted">(optional)</span></label>
                        <textarea name="about_me" id="about_me" class="input" maxlength="1200" data-count="1200"
                                  placeholder="A few warm sentences about who you are, your values and what you are looking for.">{{ old('about_me', $profile?->about_me) }}</textarea>
                        <span class="text-tiny text-muted" style="text-align:right" data-count-target></span>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('register.step', ['step' => 1]) }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back</a>
                        <button type="submit" class="btn btn-primary btn-lg">Save &amp; Continue <i class="fas fa-arrow-right"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
