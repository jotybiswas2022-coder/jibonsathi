@extends('backend.layouts.app')

@php
    $totalRows = $reports->total();

    /* The tiles are the status filter, which is what the old tab strip did. The
       "All reports" link in the page head covers the fifth view the tabs had,
       so the grid can stay the four-column one used everywhere else. */
    $queues = [
        ['key' => 'pending', 'label' => 'Pending', 'icon' => 'fa-hourglass-half', 'tone' => 'ic-warning',
            'sub' => 'Waiting on a decision'],
        ['key' => 'investigating', 'label' => 'Investigating', 'icon' => 'fa-magnifying-glass', 'tone' => 'ic-info',
            'sub' => 'Being looked into now'],
        ['key' => 'resolved', 'label' => 'Resolved', 'icon' => 'fa-circle-check', 'tone' => 'ic-success',
            'sub' => 'Closed with action taken'],
        ['key' => 'dismissed', 'label' => 'Dismissed', 'icon' => 'fa-ban', 'tone' => 'ic-muted',
            'sub' => 'Closed, nothing to do'],
    ];

    /* Reasons that can involve another member, so they read differently from a
       report that is only about the account itself. */
    $serious = ['harassment', 'inappropriate_content', 'suspicious_activity'];

    /* One string per row, so the box can filter this page without a request. */
    $rowSearch = static function (\App\Models\Report $r): string {
        return mb_strtolower(trim(implode(' ', array_filter([
            $r->reportedUser?->name,
            $r->reportedUser?->email,
            $r->reporter?->name,
            $r->reasonLabel(),
            $r->description,
        ]))));
    };

    $withStatus = function (array $params) use ($status) {
        return array_filter(array_merge(['status' => $status === 'pending' ? null : $status], $params), 'strlen');
    };
@endphp

@section('title', 'Reports')
@section('crumb', 'Trust & safety · member complaints')

@section('content')
    <div class="page-head">
        <div class="page-head-text">
            <h2>Reports</h2>
            <p>Every complaint a member has filed, and where each one stands. Work the pending queue first, then move a case on once you have decided.</p>
        </div>
        <div class="page-head-actions">
            <a href="{{ route('backend.reports.index') }}" class="btn btn-outline" @if ($status === 'pending' && ! $reason && $term === '') aria-disabled="true" tabindex="-1" style="pointer-events:none;opacity:.5" @endif>
                <i class="fas fa-layer-group"></i> All reports
            </a>
        </div>
    </div>

    @if ($counts['pending'] > 0 && $status !== 'pending')
        <div class="alert alert-warning rp-nudge">
            <i class="fas fa-hourglass-half"></i>
            <div>
                <strong>{{ $counts['pending'] }} {{ Str::plural('report', $counts['pending']) }} still {{ $counts['pending'] === 1 ? 'needs' : 'need' }} a decision.</strong>
                <a href="{{ route('backend.reports.index', ['status' => 'pending']) }}">Go to the pending queue</a>
            </div>
        </div>
    @endif

    {{-- The tiles are the status filter, so the old tab strip is gone. Each href
         is a real query, so the tiles still work with JavaScript off. --}}
    <div class="stat-grid st-tiles rp-tiles">
        @foreach ($queues as $queue)
            @php $isActive = $status === $queue['key']; @endphp
            <a class="card stat-tile st-tile {{ $isActive ? 'is-active' : '' }}"
               href="{{ route('backend.reports.index', $withStatus(['status' => $queue['key'], 'reason' => $reason, 'q' => $term ?: null])) }}"
               data-lf-tile data-lf-group="status"
               data-lf-key="{{ $queue['key'] }}" data-lf-field="status"
               data-lf-label="{{ $queue['label'] }} reports"
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
    <form method="GET" action="{{ route('backend.reports.index') }}" class="filter-bar st-toolbar rp-toolbar"
          data-lf data-lf-noun="report">
        <input type="hidden" name="status" value="{{ $status }}" data-lf-field>
        <input type="hidden" name="reason" value="{{ $reason }}" data-lf-field>

        <div class="field st-search-field">
            <label for="rpSearch">Search reports</label>
            <div class="st-search">
                <i class="fas fa-magnifying-glass"></i>
                <input type="search" name="q" id="rpSearch" class="input" value="{{ $term }}"
                       placeholder="Member, reporter or description" autocomplete="off"
                       data-lf-input aria-describedby="rpSearchHint">
                <button type="button" class="st-search-clear" data-lf-clear
                        aria-label="Clear the search box" @if ($term === '') hidden @endif>
                    <i class="fas fa-xmark"></i>
                </button>
            </div>
            <p class="hint st-hint" id="rpSearchHint" data-lf-hint>
                @if ($term !== '')
                    Showing {{ $totalRows }} {{ Str::plural('report', $totalRows) }} matching &ldquo;{{ $term }}&rdquo;.
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

    {{-- Reason is the second facet, so it lives in its own chip row rather than a
         dropdown: both facets filter the same rows, in place, and each chip says
         how many it would show. Counts follow the active status, so a chip never
         promises rows the status filter has already excluded. --}}
    <div class="rp-chips" role="group" aria-label="Filter by reason">
        <span class="rp-chips-label">Reason</span>
        <a class="rp-chip {{ $reason === null ? 'is-active' : '' }}"
           href="{{ route('backend.reports.index', $withStatus(['reason' => null, 'q' => $term ?: null])) }}"
           data-lf-tile data-lf-group="reason"
           data-lf-key="all" data-lf-field="reason" data-lf-label="any reason"
           @if ($reason === null) aria-current="true" @endif>
            All reasons
        </a>
        @foreach ($reasons as $key => $label)
            <a class="rp-chip {{ $reason === $key ? 'is-active' : '' }}"
               href="{{ route('backend.reports.index', $withStatus(['reason' => $key, 'q' => $term ?: null])) }}"
               data-lf-tile data-lf-group="reason"
               data-lf-key="{{ $key }}" data-lf-field="reason"
               data-lf-label="{{ mb_strtolower($label) }}"
               data-lf-col="reason" data-lf-val="{{ $key }}"
               data-lf-counts-for="status"
               data-lf-counts="{{ json_encode($reasonCountsByStatus ? array_map(fn ($m) => $m[$key] ?? 0, $reasonCountsByStatus) : []) }}"
               @if ($reason === $key) aria-current="true" @endif>
                {{ $label }}
                <span class="rp-chip-n" data-lf-tile-count>{{ $reasonCounts[$key] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <div class="st-result-bar">
        <span data-lf-count data-lf-total="{{ $totalRows }}">
            @if ($term !== '' || $reason)
                {{ $totalRows }} {{ Str::plural('match', $totalRows) }}
            @else
                {{ $totalRows }} {{ Str::plural('report', $totalRows) }}
            @endif
        </span>
        @if ($reports->hasPages())
            <span class="text-muted text-small">Page {{ $reports->currentPage() }} of {{ $reports->lastPage() }}</span>
        @endif
    </div>

    <div class="table-wrap st-table-wrap">
        <table class="table st-table rp-table">
            <thead>
                <tr>
                    <th>Reported Member</th>
                    <th>Reason</th>
                    <th>Reported By</th>
                    <th>Status</th>
                    <th>Reported</th>
                    <th class="st-actions-col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reports as $r)
                    @php
                        $member = $r->reportedUser;
                        $memberReports = $r->reportedUserReportTotal();
                    @endphp
                    <tr data-lf-row
                        data-status="{{ $r->status }}"
                        data-reason="{{ $r->reason }}"
                        data-search="{{ $rowSearch($r) }}">
                        <td data-label="Reported member">
                            <div class="cell-user st-cell">
                                <span class="avatar st-thumb">
                                    @if ($member?->primaryPhoto)
                                        <img src="{{ $member->primaryPhoto->url() }}" alt="" loading="lazy">
                                    @else
                                        <span class="initials">{{ $member?->initials ?? 'J' }}</span>
                                    @endif
                                </span>
                                <div class="st-cell-text">
                                    <div class="cu-name st-title" data-hl>
                                        <a href="{{ route('backend.reports.show', $r) }}">{{ $member?->name ?? 'Deleted user' }}</a>
                                    </div>
                                    <div class="cu-sub" data-hl>{{ $member?->email ?? 'No longer on Jibon Sathi' }}</div>
                                    @if ($memberReports > 1)
                                        {{-- The same member reported more than once is the
                                             strongest signal in a triage queue. --}}
                                        <span class="rp-repeat">
                                            <i class="fas fa-repeat"></i> {{ $memberReports }} reports on this member
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td data-label="Reason">
                            <span class="badge {{ in_array($r->reason, $serious, true) ? 'badge-danger' : 'badge-muted' }} rp-reason">
                                <i class="fas {{ $r->isSeriousReason() ? 'fa-triangle-exclamation' : 'fa-flag' }}"></i>
                                {{ $r->reasonLabel() }}
                            </span>
                            @if ($r->description)
                                <p class="rp-excerpt" data-hl>{{ $r->excerpt() }}</p>
                            @endif
                        </td>
                        <td data-label="Reported by">
                            <div class="st-meta">
                                <span class="st-meta-row" data-hl>
                                    <i class="fas fa-user"></i>
                                    {{ $r->reporter?->name ?? 'Member' }}
                                </span>
                                @if ($r->reporter?->id === $member?->id)
                                    <span class="st-meta-row rp-self">
                                        <i class="fas fa-circle-info"></i> Self-reported
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td data-label="Status">
                            <span class="badge badge-{{ $r->statusTone() }}"><i class="fas fa-circle"></i> {{ $r->statusLabel() }}</span>
                            @if ($r->handler)
                                <div class="rp-handler">by {{ $r->handler->name }}</div>
                            @endif
                        </td>
                        <td data-label="Reported">
                            <div class="st-meta">
                                <span class="st-meta-row">
                                    <i class="fas fa-clock"></i>
                                    {{ $r->created_at?->diffForHumans() ?? '—' }}
                                </span>
                                <span class="st-meta-row">
                                    <i class="fas fa-calendar"></i>
                                    {{ $r->created_at?->format('d M Y') ?? '—' }}
                                </span>
                            </div>
                        </td>
                        <td data-label="Actions" class="st-actions-col">
                            <div class="st-actions">
                                <a href="{{ route('backend.reports.show', $r) }}" class="st-act"
                                   title="Review case" aria-label="Review the case against {{ $member?->name ?? 'a deleted user' }}">
                                    <i class="fas fa-gavel"></i>
                                </a>
                                @if ($member)
                                    <a href="{{ route('backend.users.show', $member) }}" class="st-act"
                                       title="Open {{ $member->name }}" aria-label="Open the profile of {{ $member->name }}">
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
                                <i class="fas fa-shield-halved"></i>
                                @if ($term !== '' || $reason || $status !== 'pending')
                                    <h3>Nothing matches this view</h3>
                                    <p>No report matches the current search and filter combination.</p>
                                    <a href="{{ route('backend.reports.index') }}" class="btn btn-outline">
                                        <i class="fas fa-rotate-left"></i> Reset filters
                                    </a>
                                @else
                                    <h3>The queue is clear</h3>
                                    <p>No member complaints are waiting for a decision. Anything filed later will land here.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Shown by the filter when the current term and chips hide every row on
             this page. Other pages may still hold matches. --}}
        <div class="empty-state st-live-empty rp-live-empty" data-lf-empty hidden>
            <i class="fas fa-magnifying-glass" data-lf-empty-icon></i>
            <h3 data-lf-empty-title>Nothing on this page matches</h3>
            <p data-lf-empty-text>Reports on other pages may still match. Press Enter to search every page.</p>
            <a href="{{ route('backend.reports.index') }}" class="btn btn-outline" data-lf-empty-link hidden>
                Search every page
            </a>
        </div>
    </div>

    {{ $reports->links('backend.components.pagination') }}
@endsection
