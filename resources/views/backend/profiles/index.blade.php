@extends('backend.layouts.app')

@php
    use App\Models\Profile;

    $totalRows = $profiles->total();

    /* Four statuses fill the tile grid exactly, so unlike the reports and
       verification queues this one needs no extra "all" tile — the page head
       link covers that view instead. */
    $queues = [
        ['key' => 'pending', 'label' => 'Pending', 'icon' => 'fa-hourglass-half', 'tone' => 'ic-warning',
            'sub' => 'Waiting on a decision'],
        ['key' => 'approved', 'label' => 'Approved', 'icon' => 'fa-circle-check', 'tone' => 'ic-success',
            'sub' => 'Live in member search'],
        ['key' => 'rejected', 'label' => 'Rejected', 'icon' => 'fa-xmark', 'tone' => 'ic-danger',
            'sub' => 'Sent back to the member'],
        ['key' => 'suspended', 'label' => 'Suspended', 'icon' => 'fa-ban', 'tone' => 'ic-muted',
            'sub' => 'Blocked from the site'],
    ];

    /* One string per row, so the box can filter this page without a request. */
    $rowSearch = static function (Profile $p): string {
        return mb_strtolower(trim(implode(' ', array_filter([
            $p->user?->name,
            $p->user?->email,
            $p->user?->phone,
            $p->headline,
            $p->locationLabel(),
        ]))));
    };

    $withStatus = function (array $params) use ($status) {
        return array_filter(array_merge(['status' => $status === 'pending' ? null : $status], $params), 'strlen');
    };
@endphp

@section('title', 'Profiles')
@section('crumb', 'Trust & safety · profile moderation')

@section('content')
    <div class="page-head">
        <div class="page-head-text">
            <h2>Profiles</h2>
            <p>Every member profile and where it stands. Work the pending queue first: a thin profile has nothing to judge, so a member who has filled nothing in is either new or hiding something.</p>
        </div>
        <div class="page-head-actions">
            @php
                /* With no tile covering "all", this link is the only thing that can
                   show the mixed view is the current one. */
                $isAll = $status === 'all';
                $isDefault = $status === 'pending' && ! $band && $term === '';
            @endphp
            <a href="{{ route('backend.profiles.index') }}" class="btn btn-outline {{ $isAll ? 'is-active' : '' }}"
               @if ($isDefault) aria-disabled="true" tabindex="-1" style="pointer-events:none;opacity:.5" @endif
               @if ($isAll) aria-current="true" @endif>
                <i class="fas fa-layer-group"></i> All profiles
            </a>
        </div>
    </div>

    @if ($counts['pending'] > 0 && $status !== 'pending')
        <div class="alert alert-warning pf-nudge">
            <i class="fas fa-hourglass-half"></i>
            <div>
                <strong>{{ $counts['pending'] }} {{ Str::plural('profile', $counts['pending']) }} still {{ $counts['pending'] === 1 ? 'needs' : 'need' }} a decision.</strong>
                <a href="{{ route('backend.profiles.index', ['status' => 'pending']) }}">Go to the pending queue</a>
            </div>
        </div>
    @endif

    {{-- The tiles are the status filter, so the old tab strip is gone. Each href
         is a real query, so the tiles still work with JavaScript off. They also
         carry data-lf-nav: the status decides which rows the server returns, so
         it cannot be filtered in place the way the completion chips can. --}}
    <div class="stat-grid st-tiles pf-tiles">
        @foreach ($queues as $queue)
            @php $isActive = $status === $queue['key']; @endphp
            <a class="card stat-tile st-tile {{ $isActive ? 'is-active' : '' }}"
               href="{{ route('backend.profiles.index', $withStatus(['status' => $queue['key'], 'completion' => $band, 'q' => $term ?: null])) }}"
               data-lf-tile data-lf-group="status" data-lf-nav
               data-lf-key="{{ $queue['key'] }}" data-lf-field="status"
               data-lf-label="{{ mb_strtolower($queue['label']) }} {{ Str::plural('profile', 2) }}"
               data-lf-col="status" data-lf-val="{{ $queue['key'] }}"
               @if ($isActive) aria-current="true" @endif>
                <span class="st-ico {{ $queue['tone'] }}"><i class="fas {{ $queue['icon'] }}"></i></span>
                <span class="st-num" data-lf-tile-count>{{ $counts[$queue['key']] ?? 0 }}</span>
                <span class="st-lbl">{{ $queue['label'] }}</span>
                <span class="st-sub">{{ $queue['sub'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- A real GET form, so search works with JavaScript off and the query stays
         shareable. The JS layers instant filtering on top of it. --}}
    <form method="GET" action="{{ route('backend.profiles.index') }}" class="filter-bar st-toolbar pf-toolbar"
          data-lf data-lf-noun="profile">
        <input type="hidden" name="status" value="{{ $status }}" data-lf-field>
        <input type="hidden" name="completion" value="{{ $band }}" data-lf-field>

        <div class="field st-search-field">
            <label for="pfSearch">Search profiles</label>
            <div class="st-search">
                <i class="fas fa-magnifying-glass"></i>
                <input type="search" name="q" id="pfSearch" class="input" value="{{ $term }}"
                       placeholder="Member, email, phone or city" autocomplete="off"
                       data-lf-input aria-describedby="pfSearchHint">
                <button type="button" class="st-search-clear" data-lf-clear
                        aria-label="Clear the search box" @if ($term === '') hidden @endif>
                    <i class="fas fa-xmark"></i>
                </button>
            </div>
            <p class="hint st-hint" id="pfSearchHint" data-lf-hint>
                @if ($term !== '')
                    Showing {{ $totalRows }} {{ Str::plural('profile', $totalRows) }} matching &ldquo;{{ $term }}&rdquo;.
                @else
                    Type to filter the rows below, or press Enter to search every page.
                @endif
            </p>
        </div>

        {{-- The box filters as you type, so there is nothing to press. The hidden
             submit keeps Enter working and makes the form usable with JavaScript
             off, since a form with no submit control at all would not submit. --}}
        <button type="submit" class="sr-only" tabindex="-1" aria-hidden="true">Search</button>
    </form>

    {{-- Completion is the second facet, so it lives in its own chip row rather
         than a dropdown: both facets filter the same rows, in place, and each chip
         says how many it would show. Counts follow the active status, so a chip
         never promises rows the status filter has already excluded. --}}
    <div class="rp-chips pf-chips" role="group" aria-label="Filter by profile completion">
        <span class="rp-chips-label">Completion</span>
        <a class="rp-chip {{ $band === null ? 'is-active' : '' }}"
           href="{{ route('backend.profiles.index', $withStatus(['completion' => null, 'q' => $term ?: null])) }}"
           data-lf-tile data-lf-group="completion"
           data-lf-key="all" data-lf-field="completion" data-lf-label="any completion"
           @if ($band === null) aria-current="true" @endif>
            Any completion
        </a>
        @foreach (Profile::COMPLETION_BANDS as $key => $label)
            <a class="rp-chip {{ $band === $key ? 'is-active' : '' }}"
               href="{{ route('backend.profiles.index', $withStatus(['completion' => $key, 'q' => $term ?: null])) }}"
               data-lf-tile data-lf-group="completion"
               data-lf-key="{{ $key }}" data-lf-field="completion"
               data-lf-label="{{ mb_strtolower($label) }}"
               data-lf-col="band" data-lf-val="{{ $key }}"
               data-lf-counts-for="status"
               data-lf-counts="{{ json_encode($bandCountsByStatus ? array_map(fn ($m) => $m[$key] ?? 0, $bandCountsByStatus) : []) }}"
               @if ($band === $key) aria-current="true" @endif>
                {{ $label }}
                <span class="rp-chip-n" data-lf-tile-count>{{ $bandCounts[$key] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <div class="st-result-bar">
        <span data-lf-count data-lf-total="{{ $totalRows }}">
            @if ($term !== '' || $band)
                {{ $totalRows }} {{ Str::plural('match', $totalRows) }}
            @else
                {{ $totalRows }} {{ Str::plural('profile', $totalRows) }}
            @endif
        </span>
        @if ($profiles->hasPages())
            <span class="text-muted text-small">Page {{ $profiles->currentPage() }} of {{ $profiles->lastPage() }}</span>
        @endif
    </div>

    <div class="table-wrap st-table-wrap">
        <table class="table st-table pf-table">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Status</th>
                    <th>Completion</th>
                    <th>Identity</th>
                    <th>Submitted</th>
                    <th class="st-actions-col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($profiles as $p)
                    <tr data-lf-row
                        data-status="{{ $p->profile_status }}"
                        data-band="{{ $p->completionBand() ?? 'mid' }}"
                        data-search="{{ $rowSearch($p) }}">
                        <td data-label="Member">
                            <div class="cell-user st-cell">
                                <span class="avatar st-thumb">
                                    @if ($p->user?->primaryPhoto)
                                        <img src="{{ $p->user->primaryPhoto->url() }}" alt="" loading="lazy">
                                    @else
                                        <span class="initials">{{ $p->user?->initials ?? 'J' }}</span>
                                    @endif
                                </span>
                                <div class="st-cell-text">
                                    <div class="cu-name st-title" data-hl>
                                        <a href="{{ route('backend.profiles.show', $p) }}">{{ $p->user?->name ?? 'Deleted user' }}</a>
                                    </div>
                                    <div class="cu-sub" data-hl>{{ $p->user?->email ?? 'No longer on Jibon Sathi' }}</div>
                                    <div class="cu-sub" data-hl>{{ $p->locationLabel() }}</div>
                                </div>
                            </div>
                        </td>
                        <td data-label="Status">
                            <span class="badge badge-{{ $p->statusTone() }}"><i class="fas fa-circle"></i> {{ $p->statusLabel() }}</span>
                            @if ($p->approved_at)
                                <div class="pf-decided">on {{ $p->approved_at->format('d M Y') }}</div>
                            @endif
                        </td>
                        <td data-label="Completion">
                            {{-- A thin profile is the triage signal, so the bar is
                                 tinted rather than left as a neutral grey. --}}
                            <div class="pf-meter">
                                <div class="progress pf-meter-bar">
                                    <span class="pf-{{ $p->completionTone() }}" style="width:{{ $p->profile_completion }}%"></span>
                                </div>
                                <span class="pf-meter-n">{{ $p->profile_completion }}%</span>
                            </div>
                            @if ($p->isThin())
                                <div class="pf-thin">
                                    <i class="fas fa-triangle-exclamation"></i> Too thin to judge
                                </div>
                            @endif
                        </td>
                        <td data-label="Identity">
                            <span class="badge badge-{{ $p->verificationTone() }}">{{ $p->verificationLabel() }}</span>
                        </td>
                        <td data-label="Submitted">
                            <div class="st-meta">
                                <span class="st-meta-row">
                                    <i class="fas fa-clock"></i>
                                    {{ $p->created_at?->diffForHumans() ?? '—' }}
                                </span>
                                <span class="st-meta-row">
                                    <i class="fas fa-calendar"></i>
                                    {{ $p->created_at?->format('d M Y') ?? '—' }}
                                </span>
                            </div>
                        </td>
                        <td data-label="Actions" class="st-actions-col">
                            <div class="st-actions">
                                <a href="{{ route('backend.profiles.show', $p) }}" class="st-act"
                                   title="Review profile" aria-label="Review {{ $p->user?->name ?? 'this profile' }}">
                                    <i class="fas fa-gavel"></i>
                                </a>
                                @if ($p->user)
                                    <a href="{{ route('backend.users.show', $p->user) }}" class="st-act"
                                       title="Open {{ $p->user->name }}" aria-label="Open the account of {{ $p->user->name }}">
                                        <i class="fas fa-user"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="fas fa-id-card"></i>
                                @if ($term !== '' || $band || $status !== 'pending')
                                    <h3>Nothing matches this view</h3>
                                    <p>No profile matches the current search and filter combination.</p>
                                    <a href="{{ route('backend.profiles.index') }}" class="btn btn-outline">
                                        <i class="fas fa-rotate-left"></i> Reset filters
                                    </a>
                                @else
                                    <h3>The queue is clear</h3>
                                    <p>No profiles are waiting for a decision. Anything a member submits later will land here.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Shown by the filter when the current term and chips hide every row on
             this page. Other pages may still hold matches. --}}
        <div class="empty-state st-live-empty pf-live-empty" data-lf-empty hidden>
            <i class="fas fa-magnifying-glass" data-lf-empty-icon></i>
            <h3 data-lf-empty-title>Nothing on this page matches</h3>
            <p data-lf-empty-text>Profiles on other pages may still match. Press Enter to search every page.</p>
            <a href="{{ route('backend.profiles.index') }}" class="btn btn-outline" data-lf-empty-link hidden>
                Search every page
            </a>
        </div>
    </div>

    {{ $profiles->links('backend.components.pagination') }}
@endsection
