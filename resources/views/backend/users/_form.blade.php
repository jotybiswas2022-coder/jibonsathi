{{--
    The long form behind both "create member" and "edit every detail".

    $user   null when creating, the member being edited otherwise
    $action the URL to POST to
    $verb   the HTTP verb, for @method
--}}
@php
    use App\Models\Profile;
    use App\Support\Reference;

    $profile = $user?->profile;
    $editing = $user !== null;

    /* old() wins over the stored value so a rejected submit keeps whatever the
       admin typed instead of snapping back to the record. */
    $v = fn ($key, $value = null) => old($key, $value);
    $listed = fn ($key, $stored): array => old($key, (array) $stored);
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <i class="fas fa-circle-exclamation"></i>
        <div>
            Nothing was saved. Please fix these:
            <ul style="margin:6px 0 0;padding-left:18px">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    </div>
@endif

<form method="POST" action="{{ $action }}" enctype="multipart/form-data"
      data-confirm-title="{{ $editing ? 'Save ' . $user->name . '’s details?' : 'Create this member?' }}"
      data-confirm="{{ $editing ? 'Every field on this form replaces what is on record, and the member sees the new profile immediately.' : 'The account is created straight away and the member can sign in with the password you set.' }}"
      data-confirm-ok="{{ $editing ? 'Save details' : 'Create member' }}" data-confirm-icon="question"
      data-confirm-color="#8B1E3F" data-confirm-focus-cancel>
    @csrf
    @if ($editing) @method('PUT') @endif

    {{-- ------------------------------- Account ------------------------------- --}}
    <div class="card card-pad" style="margin-bottom:18px">
        <div class="section-block-title" style="margin-bottom:14px"><i class="fas fa-user-gear"></i> Account</div>
        <div class="field-group">
            <div class="field">
                <label for="name">Full Name <span class="text-muted">(required)</span></label>
                <input type="text" name="name" id="name" class="input" value="{{ $v('name', $user?->name) }}" required>
            </div>
            <div class="field">
                <label for="status">Account Status</label>
                <select name="status" id="status" class="input">
                    @foreach ($statuses as $key => $label)
                        <option value="{{ $key }}" @selected($v('status', $user?->status ?? 'active') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="field-group">
            <div class="field">
                <label for="email">Email <span class="text-muted">(required)</span></label>
                <input type="email" name="email" id="email" class="input" value="{{ $v('email', $user?->email) }}" required>
            </div>
            <div class="field">
                <label for="phone">Phone <span class="text-muted">(required)</span></label>
                <input type="text" name="phone" id="phone" class="input" value="{{ $v('phone', $user?->phone) }}" required>
            </div>
        </div>
        <div class="field-group">
            <div class="field">
                <label for="username">Username <span class="text-muted">(optional, made from the name if left empty)</span></label>
                <input type="text" name="username" id="username" class="input" value="{{ $v('username', $user?->username) }}">
            </div>
            <div class="field">
                <label for="password">Password @if($editing)<span class="text-muted">(leave empty to keep the current one)</span>@else<span class="text-muted">(required)</span>@endif</label>
                <input type="password" name="password" id="password" class="input" autocomplete="new-password" @unless ($editing) required @endunless>
            </div>
        </div>
        <div class="field-group">
            <div class="field">
                <label for="password_confirmation">Repeat Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="input" autocomplete="new-password">
            </div>
            <div class="field">
                <label for="is_admin">Panel access</label>
                <label class="flex gap-2" style="align-items:center;height:48px">
                    <input type="checkbox" name="is_admin" value="1" @checked(old('is_admin', $user?->is_admin)) style="width:20px;height:20px">
                    <span>Can sign in to the admin panel</span>
                </label>
            </div>
        </div>
    </div>

    {{-- ---------------------------- Basic details ---------------------------- --}}
    <div class="card card-pad" style="margin-bottom:18px">
        <div class="section-block-title" id="details-basic" style="margin-bottom:14px"><i class="fas fa-id-card"></i> Basic Details</div>
        <div class="field-group">
            <div class="field">
                <label for="gender">I Am Looking As</label>
                <select name="gender" id="gender" class="input" required>
                    @foreach (Reference::genders() as $key => $label)
                        <option value="{{ $key }}" @selected($v('gender', $profile?->gender ?? 'male') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="date_of_birth">Date Of Birth</label>
                <input type="date" name="date_of_birth" id="date_of_birth" class="input" value="{{ $v('date_of_birth', $profile?->date_of_birth?->format('Y-m-d')) }}">
            </div>
        </div>
        <div class="field-group">
            <div class="field">
                <label for="height_cm">Height (cm)</label>
                <input type="number" name="height_cm" id="height_cm" class="input" min="120" max="220" value="{{ $v('height_cm', $profile?->height_cm) }}">
            </div>
            <div class="field">
                <label for="marital_status">Marital Status</label>
                <select name="marital_status" id="marital_status" class="input">
                    <option value="">Not set</option>
                    @foreach (Reference::maritalStatuses() as $key => $label)
                        <option value="{{ $key }}" @selected($v('marital_status', $profile?->marital_status) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="field-group">
            <div class="field">
                <label for="religion">Religion</label>
                <select name="religion" id="religion" class="input">
                    <option value="">Not set</option>
                    @foreach (Reference::religions() as $key => $label)
                        <option value="{{ $key }}" @selected($v('religion', $profile?->religion) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="mother_tongue">Mother Tongue</label>
                <input type="text" name="mother_tongue" id="mother_tongue" class="input" maxlength="60" value="{{ $v('mother_tongue', $profile?->mother_tongue) }}">
            </div>
        </div>
        <div class="field">
            <label for="headline">Profile Headline</label>
            <input type="text" name="headline" id="headline" class="input" maxlength="160" value="{{ $v('headline', $profile?->headline) }}" placeholder="One line that captures the member">
        </div>
        <div class="field">
            <label for="about_me">About Me</label>
            <textarea name="about_me" id="about_me" class="input" rows="5" maxlength="1200">{{ $v('about_me', $profile?->about_me) }}</textarea>
        </div>
    </div>

    {{-- ------------------------------ Moderation ------------------------------ --}}
    <div class="card card-pad" style="margin-bottom:18px">
        <div class="section-block-title" style="margin-bottom:14px"><i class="fas fa-shield-halved"></i> Moderation</div>
        <div class="field-group">
            <div class="field">
                <label for="profile_status">Profile Status</label>
                <select name="profile_status" id="profile_status" class="input">
                    @foreach (Profile::STATUSES as $key => $label)
                        <option value="{{ $key }}" @selected($v('profile_status', $profile?->profile_status ?? 'approved') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <span class="hint">Approved means the profile shows up in search results.</span>
            </div>
            <div class="field">
                <label for="verification_status">Verification Status</label>
                <select name="verification_status" id="verification_status" class="input">
                    @foreach (Reference::verificationStatuses() as $key => $label)
                        <option value="{{ $key }}" @selected($v('verification_status', $profile?->verification_status ?? 'unverified') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <span class="hint">Verified puts the green badge on the profile.</span>
            </div>
        </div>
    </div>

    {{-- ------------------------------- Location ------------------------------- --}}
    <div class="card card-pad" style="margin-bottom:18px">
        <div class="section-block-title" id="details-location" style="margin-bottom:14px"><i class="fas fa-map-location-dot"></i> Location</div>
        <div class="field-group">
            <div class="field">
                <label for="country">Country</label>
                <select name="country" id="country" class="input">
                    <option value="">Not set</option>
                    @foreach (Reference::countries() as $key => $label)
                        <option value="{{ $key }}" @selected($v('country', $profile?->country) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="division">Division</label>
                <select name="division" id="division" class="input">
                    <option value="">Not set</option>
                    @foreach (Reference::divisionNames() as $division)
                        <option value="{{ $division }}" @selected($v('division', $profile?->division) === $division)>{{ $division }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="field-group">
            <div class="field">
                <label for="district">District</label>
                <input type="text" name="district" id="district" class="input" maxlength="80" value="{{ $v('district', $profile?->district) }}">
            </div>
            <div class="field">
                <label for="city">City / Area</label>
                <input type="text" name="city" id="city" class="input" maxlength="120" value="{{ $v('city', $profile?->city) }}">
            </div>
        </div>
    </div>

    {{-- ------------------------------- Education ------------------------------- --}}
    <div class="card card-pad" style="margin-bottom:18px">
        <div class="section-block-title" id="details-education" style="margin-bottom:14px"><i class="fas fa-graduation-cap"></i> Education</div>
        <div class="field-group">
            <div class="field">
                <label for="level">Highest Level</label>
                <input type="text" name="level" id="level" class="input" maxlength="120" value="{{ $v('level', $user?->education?->level) }}" placeholder="e.g. Bachelor">
            </div>
            <div class="field">
                <label for="degree">Degree</label>
                <input type="text" name="degree" id="degree" class="input" maxlength="120" value="{{ $v('degree', $user?->education?->degree) }}">
            </div>
        </div>
        <div class="field-group">
            <div class="field">
                <label for="institution">Institution</label>
                <input type="text" name="institution" id="institution" class="input" maxlength="150" value="{{ $v('institution', $user?->education?->institution) }}">
            </div>
            <div class="field">
                <label for="field_of_study">Field Of Study</label>
                <input type="text" name="field_of_study" id="field_of_study" class="input" maxlength="120" value="{{ $v('field_of_study', $user?->education?->field_of_study) }}">
            </div>
        </div>
        <div class="field-group">
            <div class="field">
                <label for="start_year">Start Year</label>
                <input type="number" name="start_year" id="start_year" class="input" min="1950" max="{{ now()->year }}" value="{{ $v('start_year', $user?->education?->start_year) }}">
            </div>
            <div class="field">
                <label for="end_year">End Year</label>
                <input type="number" name="end_year" id="end_year" class="input" min="1950" max="{{ now()->year }}" value="{{ $v('end_year', $user?->education?->end_year) }}">
            </div>
        </div>
    </div>

    {{-- ------------------------------ Occupation ------------------------------- --}}
    <div class="card card-pad" style="margin-bottom:18px">
        <div class="section-block-title" id="details-occupation" style="margin-bottom:14px"><i class="fas fa-briefcase"></i> Occupation</div>
        <div class="field-group">
            <div class="field">
                <label for="designation">Designation</label>
                <input type="text" name="designation" id="designation" class="input" maxlength="120" value="{{ $v('designation', $user?->occupation?->designation) }}">
            </div>
            <div class="field">
                <label for="company">Company</label>
                <input type="text" name="company" id="company" class="input" maxlength="150" value="{{ $v('company', $user?->occupation?->company) }}">
            </div>
        </div>
        <div class="field-group">
            <div class="field">
                <label for="employment_type">Employment Type</label>
                <select name="employment_type" id="employment_type" class="input">
                    <option value="">Not set</option>
                    @foreach (Reference::employmentTypes() as $key => $label)
                        <option value="{{ $key }}" @selected($v('employment_type', $user?->occupation?->employment_type) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="income_range">Income Range</label>
                <select name="income_range" id="income_range" class="input">
                    <option value="">Not set</option>
                    @foreach (Reference::incomeRanges() as $key => $label)
                        <option value="{{ $key }}" @selected($v('income_range', $user?->occupation?->income_range) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="field">
            <label for="work_location">Work Location</label>
            <input type="text" name="work_location" id="work_location" class="input" maxlength="120" value="{{ $v('work_location', $user?->occupation?->work_location) }}">
        </div>
    </div>

    {{-- -------------------------------- Family --------------------------------- --}}
    <div class="card card-pad" style="margin-bottom:18px">
        <div class="section-block-title" id="details-family" style="margin-bottom:14px"><i class="fas fa-people-group"></i> Family</div>
        <div class="field-group">
            <div class="field">
                <label for="family_type">Family Type</label>
                <select name="family_type" id="family_type" class="input">
                    <option value="">Not set</option>
                    @foreach (Reference::familyTypes() as $key => $label)
                        <option value="{{ $key }}" @selected($v('family_type', $user?->familyDetail?->family_type) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="family_status">Family Status</label>
                <select name="family_status" id="family_status" class="input">
                    <option value="">Not set</option>
                    @foreach (Reference::familyStatuses() as $key => $label)
                        <option value="{{ $key }}" @selected($v('family_status', $user?->familyDetail?->family_status) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="field-group">
            <div class="field">
                <label for="father_occupation">Father's Occupation</label>
                <input type="text" name="father_occupation" id="father_occupation" class="input" maxlength="120" value="{{ $v('father_occupation', $user?->familyDetail?->father_occupation) }}">
            </div>
            <div class="field">
                <label for="mother_occupation">Mother's Occupation</label>
                <input type="text" name="mother_occupation" id="mother_occupation" class="input" maxlength="120" value="{{ $v('mother_occupation', $user?->familyDetail?->mother_occupation) }}">
            </div>
        </div>
        <div class="field-group">
            <div class="field">
                <label for="brothers">Brothers</label>
                <input type="number" name="brothers" id="brothers" class="input" min="0" max="20" value="{{ $v('brothers', $user?->familyDetail?->brothers ?? 0) }}">
            </div>
            <div class="field">
                <label for="sisters">Sisters</label>
                <input type="number" name="sisters" id="sisters" class="input" min="0" max="20" value="{{ $v('sisters', $user?->familyDetail?->sisters ?? 0) }}">
            </div>
        </div>
        <div class="field-group">
            <div class="field">
                <label for="family_income_range">Family Income Range</label>
                <select name="family_income_range" id="family_income_range" class="input">
                    <option value="">Not set</option>
                    @foreach (Reference::incomeRanges() as $key => $label)
                        <option value="{{ $key }}" @selected($v('family_income_range', $user?->familyDetail?->family_income_range) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="field">
            <label for="about_family">About Family</label>
            <textarea name="about_family" id="about_family" class="input" rows="3" maxlength="1000">{{ $v('about_family', $user?->familyDetail?->about_family) }}</textarea>
        </div>
    </div>

    {{-- ------------------------------- Lifestyle ------------------------------- --}}
    <div class="card card-pad" style="margin-bottom:18px">
        <div class="section-block-title" id="details-lifestyle" style="margin-bottom:14px"><i class="fas fa-leaf"></i> Lifestyle</div>
        <div class="field-group">
            <div class="field">
                <label for="diet">Diet</label>
                <select name="diet" id="diet" class="input">
                    <option value="">Not set</option>
                    @foreach (Reference::diets() as $key => $label)
                        <option value="{{ $key }}" @selected($v('diet', $user?->lifestyleDetail?->diet) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="smoking">Smoking</label>
                <select name="smoking" id="smoking" class="input">
                    <option value="">Not set</option>
                    @foreach (Reference::smoking() as $key => $label)
                        <option value="{{ $key }}" @selected($v('smoking', $user?->lifestyleDetail?->smoking) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="field-group">
            <div class="field">
                <label for="drinking">Drinking</label>
                <select name="drinking" id="drinking" class="input">
                    <option value="">Not set</option>
                    @foreach (Reference::drinking() as $key => $label)
                        <option value="{{ $key }}" @selected($v('drinking', $user?->lifestyleDetail?->drinking) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="field-group">
            <div class="field">
                <label for="hobbies">Hobbies <span class="text-muted">(hold Ctrl to pick several)</span></label>
                <select name="hobbies[]" id="hobbies" class="input" multiple size="6">
                    @foreach (Reference::hobbies() as $hobby)
                        <option value="{{ $hobby }}" @selected(in_array($hobby, $listed('hobbies', $user?->lifestyleDetail?->hobbies ?? []), true))>{{ $hobby }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="interests">Interests <span class="text-muted">(hold Ctrl to pick several)</span></label>
                <select name="interests[]" id="interests" class="input" multiple size="6">
                    @foreach (Reference::interests() as $interest)
                        <option value="{{ $interest }}" @selected(in_array($interest, $listed('interests', $user?->lifestyleDetail?->interests ?? []), true))>{{ $interest }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="field">
            <label for="about_lifestyle">About Lifestyle</label>
            <textarea name="about_lifestyle" id="about_lifestyle" class="input" rows="3" maxlength="1000">{{ $v('about_lifestyle', $user?->lifestyleDetail?->about_lifestyle) }}</textarea>
        </div>
    </div>

    {{-- --------------------------- Partner preference --------------------------- --}}
    <div class="card card-pad" style="margin-bottom:18px">
        <div class="section-block-title" id="details-preference" style="margin-bottom:14px"><i class="fas fa-hand-holding-heart"></i> Partner Preference</div>
        <div class="field-group">
            <div class="field">
                <label for="preferred_gender">Preferred Gender</label>
                <select name="preferred_gender" id="preferred_gender" class="input">
                    <option value="">Not set</option>
                    @foreach (Reference::genders() as $key => $label)
                        <option value="{{ $key }}" @selected($v('preferred_gender', $user?->partnerPreference?->preferred_gender) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="preferred_country">Preferred Country</label>
                <input type="text" name="preferred_country" id="preferred_country" class="input" maxlength="80" value="{{ $v('preferred_country', $user?->partnerPreference?->preferred_country) }}">
            </div>
        </div>
        <div class="field-group">
            <div class="field">
                <label for="age_min">Age From</label>
                <input type="number" name="age_min" id="age_min" class="input" min="18" max="100" value="{{ $v('age_min', $user?->partnerPreference?->age_min) }}">
            </div>
            <div class="field">
                <label for="age_max">Age To</label>
                <input type="number" name="age_max" id="age_max" class="input" min="18" max="100" value="{{ $v('age_max', $user?->partnerPreference?->age_max) }}">
            </div>
        </div>
        <div class="field-group">
            <div class="field">
                <label for="height_min_cm">Height From (cm)</label>
                <input type="number" name="height_min_cm" id="height_min_cm" class="input" min="120" max="220" value="{{ $v('height_min_cm', $user?->partnerPreference?->height_min_cm) }}">
            </div>
            <div class="field">
                <label for="height_max_cm">Height To (cm)</label>
                <input type="number" name="height_max_cm" id="height_max_cm" class="input" min="120" max="220" value="{{ $v('height_max_cm', $user?->partnerPreference?->height_max_cm) }}">
            </div>
        </div>
        <div class="field-group">
            <div class="field">
                <label for="preferred_division">Preferred Division</label>
                <select name="preferred_division" id="preferred_division" class="input">
                    <option value="">Not set</option>
                    @foreach (Reference::divisionNames() as $division)
                        <option value="{{ $division }}" @selected($v('preferred_division', $user?->partnerPreference?->preferred_division) === $division)>{{ $division }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="preferred_district">Preferred District</label>
                <input type="text" name="preferred_district" id="preferred_district" class="input" maxlength="80" value="{{ $v('preferred_district', $user?->partnerPreference?->preferred_district) }}">
            </div>
        </div>
        <div class="field-group">
            <div class="field">
                <label for="religions">Preferred Religions <span class="text-muted">(hold Ctrl to pick several)</span></label>
                <select name="religions[]" id="religions" class="input" multiple size="5">
                    @foreach (Reference::religions() as $key => $label)
                        <option value="{{ $key }}" @selected(in_array($key, $listed('religions', $user?->partnerPreference?->religions ?? []), true))>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="marital_statuses">Accepted Marital Statuses <span class="text-muted">(hold Ctrl to pick several)</span></label>
                <select name="marital_statuses[]" id="marital_statuses" class="input" multiple size="5">
                    @foreach (Reference::maritalStatuses() as $key => $label)
                        <option value="{{ $key }}" @selected(in_array($key, $listed('marital_statuses', $user?->partnerPreference?->marital_statuses ?? []), true))>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="field">
            <label for="notes">Notes On The Preference</label>
            <textarea name="notes" id="notes" class="input" rows="3" maxlength="1000">{{ $v('notes', $user?->partnerPreference?->notes) }}</textarea>
        </div>
    </div>

    {{-- --------------------------------- Photo --------------------------------- --}}
    <div class="card card-pad" style="margin-bottom:18px">
        <div class="section-block-title" id="details-photo" style="margin-bottom:14px"><i class="fas fa-camera"></i> Photo</div>
        <div class="field">
            <label for="photo">{{ $editing ? 'Add Another Photo' : 'Profile Photo' }} <span class="text-muted">(optional)</span></label>
            <input type="file" name="photo" id="photo" class="input" accept="image/jpeg,image/png,image/webp">
            <span class="hint">JPG, PNG or WEBP &middot; up to 4 MB.
                @if ($editing && $user->photos->isNotEmpty())
                    This member already has {{ $user->photos->count() }} photo(s); the new one is added to the gallery.
                @else
                    The first photo becomes the profile picture.
                @endif
            </span>
        </div>
    </div>

    <div class="card card-pad">
        <div class="flex gap-2" style="justify-content:space-between;flex-wrap:wrap">
            <a href="{{ $editing ? route('backend.users.show', $user) : route('backend.users.index') }}" class="btn btn-outline">Cancel</a>
            <button class="btn btn-primary"><i class="fas fa-save"></i> {{ $editing ? 'Save All Details' : 'Create Member' }}</button>
        </div>
    </div>
</form>
