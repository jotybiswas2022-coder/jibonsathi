@extends('frontend.layouts.app')

@section('title', 'Discover Matches')

@php $selected = fn (string $key): mixed => $filters[$key] ?? null; @endphp

@section('content')
<div class="container discover-page">
    <div class="page-head">
        <div>
            <h1 class="page-title">Discover Matches</h1>
            <p class="page-sub">Filter to find someone who feels right for you.</p>
        </div>
    </div>

    <div class="discover-layout">
        <form action="{{ route('discover.index') }}" method="GET" data-discover-filter class="filter-panel-wrap">
            <aside class="filter-panel">
                <div class="filter-head">
                    <h3><i class="fas fa-sliders-h"></i> Filters</h3>
                    <a href="{{ route('discover.index') }}" class="text-small text-muted filter-reset">Reset</a>
                    <button type="button" class="filter-close" data-filter-close aria-label="Close filters">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>

                <div class="filter-row">
                    <label class="filter-label">I'm looking for</label>
                    <div class="segmented">
                        @foreach (\App\Support\Reference::genders() as $key => $label)
                            <label class="seg-opt">
                                <input type="radio" name="gender" value="{{ $key }}" @checked($selected('gender') === $key)>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="filter-row">
                    <label class="filter-label">Age</label>
                    <div class="filter-pair">
                        <input type="number" name="age_from" min="18" max="80" class="input" placeholder="From"
                               value="{{ $selected('age_from') }}">
                        <span class="text-muted">to</span>
                        <input type="number" name="age_to" min="18" max="90" class="input" placeholder="To"
                               value="{{ $selected('age_to') }}">
                    </div>
                </div>

                <div class="filter-row">
                    <label class="filter-label">Location</label>
                    <input type="text" name="location" class="input" placeholder="City, district or division"
                           value="{{ $selected('location') }}">
                </div>

                <div class="filter-row">
                    <label class="filter-label">Religion</label>
                    <select name="religion" class="input">
                        <option value="">Any religion</option>
                        @foreach (\App\Support\Reference::religions() as $key => $label)
                            <option value="{{ $key }}" @selected($selected('religion') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-row">
                    <label class="filter-label">Marital Status</label>
                    <select name="marital_status" class="input">
                        <option value="">Any status</option>
                        @foreach (\App\Support\Reference::maritalStatuses() as $key => $label)
                            <option value="{{ $key }}" @selected($selected('marital_status') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-row">
                    <label class="filter-label">Education</label>
                    <select name="education" class="input">
                        <option value="">Any education</option>
                        @foreach (\App\Support\Reference::educationLevels() as $key => $label)
                            <option value="{{ $key }}" @selected($selected('education') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-row">
                    <label class="filter-label">Profession</label>
                    <input type="text" name="profession" class="input" placeholder="e.g. Engineer"
                           value="{{ $selected('profession') }}">
                </div>

                <div class="filter-row">
                    <label class="filter-label">Income (BDT)</label>
                    <select name="income" class="input">
                        <option value="">Any income</option>
                        @foreach (\App\Support\Reference::incomeRanges() as $key => $label)
                            <option value="{{ $key }}" @selected($selected('income') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-row">
                    <label class="filter-label">Family Type</label>
                    <select name="family_type" class="input">
                        <option value="">Any type</option>
                        @foreach (\App\Support\Reference::familyTypes() as $key => $label)
                            <option value="{{ $key }}" @selected($selected('family_type') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-row">
                    <label class="filter-label">Lifestyle</label>
                    <div class="filter-checks">
                        <label class="radio-line">
                            <input type="radio" name="diet" value="vegetarian" @checked($selected('diet') === 'vegetarian')>
                            Vegetarian
                        </label>
                        <label class="radio-line">
                            <input type="radio" name="diet" value="non_vegetarian" @checked($selected('diet') === 'non_vegetarian')>
                            Non-Vegetarian
                        </label>
                        <label class="radio-line">
                            <input type="radio" name="smoking" value="never" @checked($selected('smoking') === 'never')>
                            Non-Smoker
                        </label>
                        <label class="radio-line">
                            <input type="radio" name="drinking" value="never" @checked($selected('drinking') === 'never')>
                            Non-Drinker
                        </label>
                    </div>
                </div>

                <div class="filter-row">
                    <label class="filter-label">Verified only</label>
                    <label class="switch-row">
                        <input type="checkbox" name="verified" value="1" @checked(filled($filters['verified'] ?? null))>
                        <span class="switch"></span>
                        <span class="text-small text-muted">Show verified profiles</span>
                    </label>
                </div>

                @auth
                    <div class="filter-row mb-0" style="border-bottom:0">
                        <label class="switch-row">
                            <input type="checkbox" name="apply_preference" value="1" @checked(filled($filters['apply_preference'] ?? null))>
                            <span class="switch"></span>
                            <span class="text-small">Use my partner preferences</span>
                        </label>
                    </div>
                @endauth

                {{-- Mobile-only: closes the filter sheet; filters apply live. --}}
                <button type="button" class="btn btn-primary btn-block filter-apply" data-filter-close>
                    <i class="fas fa-circle-check"></i> Show results
                </button>
            </aside>

            <div class="discover-main">
                <div class="discover-toolbar">
                    <button type="button" class="btn btn-outline btn-sm filter-toggle" data-filter-toggle>
                        <i class="fas fa-sliders-h"></i> Filters
                    </button>
                    <p class="text-muted text-small m-0 discover-toolbar-hint">
                        <i class="fas fa-filter"></i> Refine your search
                    </p>
                    <select name="sort" class="input discover-sort">
                        @foreach ($sorts as $key => $label)
                            <option value="{{ $key }}" @selected(($filters['sort'] ?? 'recommended') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div data-results>
                    @include('frontend.discover.partials.results')
                </div>
            </div>
        </form>
    </div>
</div>
@endsection