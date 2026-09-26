@extends('backend.layouts.app')

@php
    $status = $status ?? 'all';
    $filters = $filters ?? [];
    $term = trim((string) ($filters['q'] ?? ''));
    $onPage = $stories->count();
    $totalRows = $stories->total();

    /* The tiles double as the status filter, so each one links to the same
       query the old tab strip used plus a featured view. */
    $views = [
        ['key' => 'all', 'label' => 'All stories', 'icon' => 'fa-layer-group', 'tone' => 'ic-brand',
            'sub' => 'Everything in the library'],
        ['key' => 'published', 'label' => 'Published', 'icon' => 'fa-circle-check', 'tone' => 'ic-success',
            'sub' => 'Live on the website'],
        ['key' => 'draft', 'label' => 'Drafts', 'icon' => 'fa-pen-ruler', 'tone' => 'ic-warning',
            'sub' => 'Hidden from visitors'],
        ['key' => 'featured', 'label' => 'Featured', 'icon' => 'fa-crown', 'tone' => 'ic-accent',
            'sub' => 'Highlighted to visitors'],
    ];
@endphp

@section('title', 'Success Stories')
@section('crumb', 'Content · Couple stories on the site')

@section('content')
    <div class="page-head">
        <div class="page-head-text">
            <h2>Success Stories</h2>
            <p>Publish real couple journeys on the public success-stories page, and feature the best ones.</p>
        </div>
        <div class="page-head-actions">
            <a href="{{ route('success-stories.index') }}" target="_blank" rel="noopener" class="btn btn-outline">
                <i class="fas fa-external-link"></i> View live page
            </a>
            <a href="{{ route('backend.success-stories.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Story
            </a>
        </div>
    </div>

    {{-- Status tiles double as the filter, so the old tab strip is gone. --}}
    <div class="stat-grid st-tiles">
        @foreach ($views as $view)
            @php $isActive = $status === $view['key']; @endphp
            {{-- The href is a real filter link, so the tiles still work without JS.
                 With JS on, the click is intercepted and the rows are filtered in
                 place instead of reloading the page. --}}
            <a class="card stat-tile st-tile {{ $isActive ? 'is-active' : '' }}"
               href="{{ route('backend.success-stories.index', array_filter(['status' => $view['key'] === 'all' ? null : $view['key'], 'q' => $term ?: null])) }}"
               data-status-filter="{{ $view['key'] }}"
               @if ($isActive) aria-current="true" @endif>
                <span class="st-ico {{ $view['tone'] }}"><i class="fas {{ $view['icon'] }}"></i></span>
                <span class="st-num" data-st-count>{{ $counts[$view['key']] ?? 0 }}</span>
                <span class="st-lbl">{{ $view['label'] }}</span>
                <span class="st-sub">{{ $view['sub'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- The form is a real GET search, so it still works with JavaScript off and
         keeps the query shareable. The JS layers instant filtering on top and
         only searches this page; Enter runs the full search across every page. --}}
    <form method="GET" action="{{ route('backend.success-stories.index') }}" class="filter-bar st-toolbar" data-live-search>
        <input type="hidden" name="status" value="{{ $status === 'all' ? '' : $status }}" data-status-input>

        <div class="field st-search-field">
            <label for="stSearch">Search stories</label>
            <div class="st-search">
                <i class="fas fa-magnifying-glass"></i>
                <input type="search" name="q" id="stSearch" class="input" value="{{ $term }}"
                       placeholder="Title, couple or location" autocomplete="off"
                       data-live-search-input aria-describedby="stSearchHint">
                <button type="button" class="st-search-clear" data-live-search-clear
                        aria-label="Clear the search box" @if ($term === '') hidden @endif>
                    <i class="fas fa-xmark"></i>
                </button>
            </div>
            <p class="hint st-hint" id="stSearchHint" data-live-search-hint>
                @if ($term !== '')
                    Showing {{ $totalRows }} {{ Str::plural('story', $totalRows) }} matching &ldquo;{{ $term }}&rdquo;.
                @else
                    Type to filter the rows below, or press Enter to search every page.
                @endif
            </p>
        </div>

        {{-- The box filters as you type, so there is nothing to press. A hidden
             submit button keeps Enter working in every browser and with JavaScript
             off, since a form with no submit control at all would not submit. --}}
        <button type="submit" class="sr-only" tabindex="-1" aria-hidden="true">Search</button>

        <div class="field actions">
            <a href="{{ route('backend.success-stories.index') }}" class="btn btn-outline" data-live-search-reset
               @if ($term === '' && $status === 'all') aria-disabled="true" tabindex="-1" style="pointer-events:none;opacity:.5" @endif>
                <i class="fas fa-rotate-left"></i> Reset
            </a>
        </div>
    </form>

    <div class="st-result-bar">
        <span data-live-search-count data-live-search-total="{{ $totalRows }}">
            @if ($term !== '')
                {{ $totalRows }} {{ Str::plural('match', $totalRows) }}
            @else
                {{ $totalRows }} {{ Str::plural('story', $totalRows) }}
            @endif
        </span>
        @if ($stories->hasPages())
            <span class="text-muted text-small">Page {{ $stories->currentPage() }} of {{ $stories->lastPage() }}</span>
        @endif
    </div>

    <div class="table-wrap st-table-wrap">
        <table class="table st-table">
            <thead>
                <tr>
                    <th>Story</th>
                    <th>Details</th>
                    <th>Visibility</th>
                    <th>Featured</th>
                    <th class="st-actions-col">Actions</th>
                </tr>
            </thead>
            <tbody data-live-search-rows>
                @forelse ($stories as $story)
                    <tr data-story-row
                        data-status="{{ $story->is_published ? 'published' : 'draft' }}"
                        data-featured="{{ $story->is_featured ? '1' : '0' }}"
                        data-search="{{ mb_strtolower($story->title.' '.$story->groom_name.' '.$story->bride_name.' '.$story->location) }}">
                        <td data-label="Story">
                            <div class="cell-user st-cell">
                                <span class="avatar st-thumb">
                                    @if ($story->photoUrl())
                                        <img src="{{ $story->photoUrl() }}" alt="" loading="lazy">
                                    @else
                                        <span class="initials">{{ $story->initials() }}</span>
                                    @endif
                                </span>
                                <div class="st-cell-text">
                                    <div class="cu-name st-title" data-hl>
                                        <a href="{{ route('backend.success-stories.edit', $story) }}">{{ $story->title }}</a>
                                    </div>
                                    <div class="cu-sub" data-hl>{{ $story->coupleLabel() }}</div>
                                </div>
                            </div>
                        </td>
                        <td data-label="Details">
                            <div class="st-meta">
                                <span class="st-meta-row" data-hl>
                                    <i class="fas fa-location-dot"></i>{{ $story->location ?: '—' }}
                                </span>
                                <span class="st-meta-row">
                                    <i class="fas fa-ring"></i>
                                    {{ $story->married_on?->format('M Y') ?? 'Date not set' }}
                                </span>
                                <span class="st-meta-row">
                                    <i class="fas fa-arrow-down-1-9"></i>Order {{ $story->sort_order }}
                                </span>
                            </div>
                        </td>
                        <td data-label="Visibility">
                            <form method="POST" action="{{ route('backend.success-stories.toggle-publish', $story) }}"
                                  data-confirm-title="{{ $story->is_published ? 'Unpublish this story?' : 'Publish this story?' }}"
                                  data-confirm="{{ $story->is_published ? 'It disappears from the public success-stories page.' : 'It goes live on the public success-stories page straight away.' }}"
                                  data-confirm-ok="{{ $story->is_published ? 'Unpublish' : 'Publish' }}"
                                  data-confirm-icon="question"
                                  data-confirm-color="{{ $story->is_published ? '#6B7280' : '#16A34A' }}"
                                  data-confirm-focus-cancel>
                                @csrf
                                <button class="st-toggle {{ $story->is_published ? 'is-on' : 'is-off' }}">
                                    <span class="st-toggle-dot"></span>
                                    {{ $story->is_published ? 'Published' : 'Draft' }}
                                </button>
                            </form>
                        </td>
                        <td data-label="Featured">
                            @if ($story->is_featured)
                                <span class="badge badge-accent"><i class="fas fa-crown"></i> Featured</span>
                            @else
                                <span class="badge badge-muted">No</span>
                            @endif
                        </td>
                        <td data-label="Actions" class="st-actions-col">
                            <div class="st-actions">
                                <a href="{{ route('success-stories.show', $story) }}" target="_blank" rel="noopener"
                                   class="st-act" title="View on the website" aria-label="View {{ $story->title }} on the website">
                                    <i class="fas fa-external-link"></i>
                                </a>
                                <a href="{{ route('backend.success-stories.edit', $story) }}" class="st-act"
                                   title="Edit story" aria-label="Edit {{ $story->title }}">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form method="POST" action="{{ route('backend.success-stories.destroy', $story) }}"
                                      data-confirm-title="Delete this success story?"
                                      data-confirm="{{ $story->coupleLabel() }} will be removed permanently."
                                      data-confirm-ok="Delete story" data-confirm-icon="error"
                                      data-confirm-color="#DC2626">
                                    @csrf @method('DELETE')
                                    <button class="st-act is-danger" title="Delete story"
                                            aria-label="Delete {{ $story->title }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr data-story-empty>
                        <td colspan="5">
                            <div class="empty-state">
                                <i class="fas fa-heart"></i>
                                @if ($term !== '')
                                    <h3>No story matches &ldquo;{{ $term }}&rdquo;</h3>
                                    <p>Try a different title, couple name or location, or reset the search.</p>
                                    <a href="{{ route('backend.success-stories.index', ['status' => $status === 'all' ? null : $status]) }}"
                                       class="btn btn-outline">Clear search</a>
                                @else
                                    <h3>No stories here yet</h3>
                                    <p>{{ $status === 'featured' ? 'Mark a story as featured and it will show up here.' : 'Create your first success story to share a real couple\'s journey.' }}</p>
                                    <a href="{{ route('backend.success-stories.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> New Story
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Shown by the live search when the current term and status hide every
             row on the page. The copy and the link are rewritten by the filter so
             it never claims a page is empty when other pages still have matches. --}}
        <div class="empty-state st-live-empty" data-live-search-empty hidden>
            <i class="fas fa-magnifying-glass" data-live-empty-icon></i>
            <h3 data-live-empty-title>Nothing on this page matches</h3>
            <p data-live-empty-text>The stories on other pages may still match. Press Enter to search every page.</p>
            <a href="{{ route('backend.success-stories.index') }}" class="btn btn-outline" data-live-empty-link hidden>
                Search every page
            </a>
        </div>
    </div>

    {{ $stories->links('backend.components.pagination') }}
@endsection
