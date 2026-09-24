@php
    $p = $profile->profile;
    $family = $profile->familyDetail;
    $life = $profile->lifestyleDetail;
    $edu = $profile->education;
    $occ = $profile->occupation;
    $R = \App\Support\Reference::class;
@endphp

<div class="bio-grid">
    {{-- Basic information --}}
    <div class="card card-pad bio-card">
        <h3 class="card-title"><i class="fas fa-id-card"></i> Basic Information</h3>
        <dl class="bio-list">
            <div><dt>Full Name</dt><dd>{{ $profile->name }}</dd></div>
            <div><dt>Age</dt><dd>{{ $p?->ageGroup() ?? '—' }}</dd></div>
            <div><dt>Height</dt><dd>{{ $p?->heightLabel() ?? '—' }}</dd></div>
            <div><dt>Gender</dt><dd>{{ $profile->genderLabel() }}</dd></div>
            <div><dt>Marital Status</dt><dd>{{ $R::label($p?->marital_status, 'marital_status') }}</dd></div>
            <div><dt>Religion</dt><dd>{{ $R::label($p?->religion, 'religion') }}</dd></div>
            <div><dt>Mother Tongue</dt><dd>{{ $p?->mother_tongue ?: '—' }}</dd></div>
            <div><dt>Country</dt><dd>{{ $R::label($p?->country, 'country') }}</dd></div>
            <div><dt>Division</dt><dd>{{ $p?->division ?: '—' }}</dd></div>
            <div><dt>District</dt><dd>{{ $p?->district ?: '—' }}</dd></div>
            <div><dt>City / Area</dt><dd>{{ $p?->city ?: '—' }}</dd></div>
        </dl>
    </div>

    {{-- Education & career --}}
    <div class="card card-pad bio-card">
        <h3 class="card-title"><i class="fas fa-graduation-cap"></i> Education & Career</h3>
        <dl class="bio-list">
            <div><dt>Highest Education</dt><dd>{{ $R::label($edu?->level, 'education') }}</dd></div>
            @if ($edu?->degree)
                <div><dt>Degree</dt><dd>{{ $edu->degree }}</dd></div>
            @endif
            @if ($edu?->field_of_study)
                <div><dt>Field of Study</dt><dd>{{ $edu->field_of_study }}</dd></div>
            @endif
            @if ($edu?->institution)
                <div><dt>Institution</dt><dd>{{ $edu->institution }}</dd></div>
            @endif
            @if ($edu?->end_year)
                <div><dt>Passing Year</dt><dd>{{ $edu->end_year }}</dd></div>
            @endif
            <div><dt>Profession</dt><dd>{{ $occ?->designation ?: '—' }}</dd></div>
            @if ($occ?->company)
                <div><dt>Company</dt><dd>{{ $occ->company }}</dd></div>
            @endif
            @if ($occ?->employment_type)
                <div><dt>Employment Type</dt><dd>{{ $R::label($occ->employment_type, 'employment') }}</dd></div>
            @endif
            @if ($occ?->income_range)
                <div><dt>Annual Income</dt><dd>{{ $R::label($occ->income_range, 'income') }}</dd></div>
            @endif
            @if ($occ?->work_location)
                <div><dt>Work Location</dt><dd>{{ $occ->work_location }}</dd></div>
            @endif
        </dl>
    </div>

    {{-- Family --}}
    @if ($family)
        <div class="card card-pad bio-card">
            <h3 class="card-title"><i class="fas fa-house-user"></i> Family Details</h3>
            <dl class="bio-list">
                <div><dt>Family Type</dt><dd>{{ $R::label($family?->family_type, 'family_type') }}</dd></div>
                <div><dt>Family Status</dt><dd>{{ $R::label($family?->family_status, 'family_status') }}</dd></div>
                @if ($family?->father_occupation)
                    <div><dt>Father</dt><dd>{{ $family->father_occupation }}</dd></div>
                @endif
                @if ($family?->mother_occupation)
                    <div><dt>Mother</dt><dd>{{ $family->mother_occupation }}</dd></div>
                @endif
                <div><dt>Brothers</dt><dd>{{ $family?->brothers ?? 0 }}</dd></div>
                <div><dt>Sisters</dt><dd>{{ $family?->sisters ?? 0 }}</dd></div>
                @if ($family?->family_income_range)
                    <div><dt>Family Income</dt><dd>{{ $R::label($family->family_income_range, 'income') }}</dd></div>
                @endif
            </dl>
            @if ($family?->about_family)
                <p class="text-muted text-small" style="margin-top:12px;white-space:pre-wrap">{{ $family->about_family }}</p>
            @endif
        </div>
    @endif

    {{-- Lifestyle --}}
    @if ($life)
        <div class="card card-pad bio-card">
            <h3 class="card-title"><i class="fas fa-spa"></i> Lifestyle</h3>
            <dl class="bio-list">
                <div><dt>Diet</dt><dd>{{ $R::label($life?->diet, 'diet') }}</dd></div>
                <div><dt>Smoking</dt><dd>{{ $R::label($life?->smoking, 'smoking') }}</dd></div>
                <div><dt>Drinking</dt><dd>{{ $R::label($life?->drinking, 'drinking') }}</dd></div>
            </dl>

            @php($tags = array_merge($life?->hobbies ?? [], $life?->interests ?? []))
            @if (! empty($tags))
                <div class="section-title mt-4" style="font-size:13px"><i class="fas fa-heart"></i> Hobbies & Interests</div>
                <div class="chip-list">
                    @foreach ($tags as $tag)
                        <span class="chip static">{{ $tag }}</span>
                    @endforeach
                </div>
            @endif

            @if ($life?->about_lifestyle)
                <p class="text-muted text-small" style="margin-top:12px;white-space:pre-wrap">{{ $life->about_lifestyle }}</p>
            @endif
        </div>
    @endif
</div>