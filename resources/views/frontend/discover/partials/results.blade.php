@php
    $f = $filters;

    // Removing a chip keeps every other active filter in the query string.
    $removeUrl = function (array $keys) use ($f) {
        $query = collect($f)->except(array_merge(['page'], $keys))
            ->filter(fn ($v) => $v !== null && $v !== '')->all();
        return route('discover.index', $query);
    };

    $chips = [];

    if (filled($f['age_from'] ?? null) || filled($f['age_to'] ?? null)) {
        $chips[] = [
            'icon' => 'fa-cake-candles',
            'label' => 'Age '.($f['age_from'] ?? 'Any').'–'.($f['age_to'] ?? 'Any'),
            'url' => $removeUrl(['age_from', 'age_to']),
        ];
    }

    $optionFilters = [
        'gender' => [\App\Support\Reference::genders(), 'fa-venus-mars'],
        'religion' => [\App\Support\Reference::religions(), 'fa-mosque'],
        'marital_status' => [\App\Support\Reference::maritalStatuses(), 'fa-ring'],
        'education' => [\App\Support\Reference::educationLevels(), 'fa-graduation-cap'],
        'income' => [\App\Support\Reference::incomeRanges(), 'fa-bangladeshi-taka-sign'],
        'family_type' => [\App\Support\Reference::familyTypes(), 'fa-people-roof'],
    ];
    foreach ($optionFilters as $key => [$options, $icon]) {
        if (filled($f[$key] ?? null)) {
            $chips[] = [
                'icon' => $icon,
                'label' => $options[$f[$key]] ?? ucfirst(str_replace('_', ' ', $f[$key])),
                'url' => $removeUrl([$key]),
            ];
        }
    }

    foreach (['location' => 'fa-location-dot', 'profession' => 'fa-briefcase'] as $key => $icon) {
        if (filled($f[$key] ?? null)) {
            $chips[] = ['icon' => $icon, 'label' => $f[$key], 'url' => $removeUrl([$key])];
        }
    }

    $toggleFilters = [
        'verified' => ['label' => 'Verified only', 'icon' => 'fa-circle-check'],
        'apply_preference' => ['label' => 'My preferences', 'icon' => 'fa-wand-magic-sparkles'],
        'diet' => ['labels' => ['vegetarian' => 'Vegetarian', 'non_vegetarian' => 'Non-Vegetarian'], 'icon' => 'fa-utensils'],
        'smoking' => ['labels' => ['never' => 'Non-smoker'], 'icon' => 'fa-ban-smoking'],
        'drinking' => ['labels' => ['never' => 'Non-drinker'], 'icon' => 'fa-wine-glass'],
    ];
    foreach ($toggleFilters as $key => $meta) {
        if (filled($f[$key] ?? null)) {
            $chips[] = [
                'icon' => $meta['icon'],
                'label' => $meta['label'] ?? ($meta['labels'][$f[$key]] ?? ucfirst($f[$key])),
                'url' => $removeUrl([$key]),
            ];
        }
    }
@endphp

@if (! empty($chips))
    <div class="active-filters">
        <span class="af-label"><i class="fas fa-sliders-h"></i> Active</span>
        @foreach ($chips as $chip)
            <a href="{{ $chip['url'] }}" class="active-chip">
                <i class="fas {{ $chip['icon'] }}"></i> {{ $chip['label'] }}
                <i class="fas fa-xmark"></i>
            </a>
        @endforeach
        <a href="{{ route('discover.index') }}" class="active-chip clear">
            <i class="fas fa-rotate-left"></i> Clear all
        </a>
    </div>
@endif

<div class="results-meta" data-result-count>
    <p><strong>{{ $results->total() }}</strong> member{{ $results->total() === 1 ? '' : 's' }} found</p>
</div>

@if ($results->isEmpty())
    <x-frontend::empty-state :icon="'fa-user-slash'" :title="'No matches yet'"
        :description="'Try broadening your filters to see more members.'" />
@else
    <div class="grid-3 cards-grid">
        @foreach ($results as $result)
            <x-frontend::profile-card :user="$result" :score="$scores[$result->id] ?? null" />
        @endforeach
    </div>

    <div class="mt-5">
        {{ $results->links('frontend.components.pagination') }}
    </div>
@endif
