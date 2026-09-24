@php
    $selected = fn (?string $key, $default = null) => isset($filters[$key]) ? $filters[$key] : ($default ?? '');
@endphp

<div class="discover-toolbar" data-result-count>
    <p class="text-muted text-small m-0">
        <strong style="color:var(--text)">{{ $results->total() }}</strong> member{{ $results->total() === 1 ? '' : 's' }} found
    </p>
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