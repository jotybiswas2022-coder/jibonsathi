@extends('backend.layouts.app')

@php
    use App\Models\Verification;

    $totalRows = $verifications->total();

    /* The tiles are the status filter, which is what the old tab strip did. The
       "All verifications" link in the page head covers the fourth view the tabs
       had, so the grid can stay the four-column one used everywhere else. */
    $queues = [
        ['key' => 'pending', 'label' => 'Pending', 'icon' => 'fa-hourglass-half', 'tone' => 'ic-warning',
            'sub' => 'Waiting on a decision'],
        ['key' => 'approved', 'label' => 'Approved', 'icon' => 'fa-circle-check', 'tone' => 'ic-success',
            'sub' => 'Identity confirmed'],
        ['key' => 'rejected', 'label' => 'Rejected', 'icon' => 'fa-ban', 'tone' => 'ic-muted',
            'sub' => 'Sent back to the member'],
    ];

    /* One string per row, so the box can filter this page without a request. */
    $rowSearch = static function (Verification $v): string {
        return mb_strtolower(trim(implode(' ', array_filter([
            $v->user?->name,
            $v->user?->email,
            $v->user?->phone,
            $v->typeLabel(),
            $v->document_type,
            $v->admin_note,
        ]))));
    };

    $withStatus = function (array $params) use ($status) {
        return array_filter(array_merge(['status' => $status === 'pending' ? null : $status], $params), 'strlen');
    };
@endphp

@section('title', 'Identity Verifications')
@section('crumb', 'Trust & safety · identity documents')

@section('content')
    <div class="page-head">
        <div class="page-head-text">
            <h2>Identity Verifications</h2>
            <p>Every identity document a member has sent, and where each one stands. Check the document against the profile it belongs to, then record the decision.</p>
        </div>
        <div class="page-head-actions">
            <a href="{{ route('backend.verification.index') }}" class="btn btn-outline" @if ($status === 'pending' && ! $type && $term === '') aria-disabled="true" tabindex="-1" style="pointer-events:none;opacity:.5" @endif>
                <i class="fas fa-layer-group"></i> All verifications
            </a>
        </div>
    </div>

    @if ($counts['pending'] > 0 && $status !== 'pending')
        <div class="alert alert-warning vf-nudge">
            <i class="fas fa-hourglass-half"></i>
            <div>
                <strong>{{ $counts['pending'] }} {{ Str::plural('verification', $counts['pending']) }} still {{ $counts['pending'] === 1 ? 'needs' : 'need' }} a decision.</strong>
                <a href="{{ route('backend.verification.index', ['status' => 'pending']) }}">Go to the pending queue</a>
            </div>
        </div>
    @endif

    {{-- The tiles are the status filter, so the old tab strip is gone. Each href
         is a real query, so the tiles still work with JavaScript off. --}}
    <div class="stat-grid st-tiles vf-tiles">
        @foreach ($queues as $queue)
            @php $isActive = $status === $queue['key']; @endphp
            <a class="card stat-tile st-tile {{ $isActive ? 'is-active' : '' }}"
               href="{{ route('backend.verification.index', $withStatus(['status' => $queue['key'], 'type' => $type, 'q' => $term ?: null])) }}"
               data-lf-tile data-lf-group="status"
               data-lf-key="{{ $queue['key'] }}" data-lf-field="status"
               data-lf-label="{{ $queue['label'] }} {{ Str::plural('verification', 2) }}"
               data-lf-col="status" data-lf-val="{{ $queue['key'] }}"
               @if ($isActive) aria-current="true" @endif>
                <span class="st-ico {{ $queue['tone'] }}"><i class="fas {{ $queue['icon'] }}"></i></span>
                <span class="st-num" data-lf-tile-count>{{ $counts[$queue['key']] ?? 0 }}</span>
                <span class="st-lbl">{{ $queue['label'] }}</span>
                <span class="st-sub">{{ $queue['sub'] }}</span>
            </a>
        @endforeach

        {{-- The fourth tile is the "all" view the tab strip used to offer, so the
             grid keeps to four columns instead of leaving a hole. --}}
        <a class="card stat-tile st-tile {{ $status === 'all' ? 'is-active' : '' }}"
           href="{{ route('backend.verification.index', array_filter(['status' => null, 'type' => $type, 'q' => $term ?: null])) }}"
           data-lf-tile data-lf-group="status"
           data-lf-key="all" data-lf-field="status" data-lf-label="every status"
           @if ($status === 'all') aria-current="true" @endif>
            <span class="st-ico ic-brand"><i class="fas fa-layer-group"></i></span>
            <span class="st-num" data-lf-tile-count>{{ $counts['all'] ?? 0 }}</span>
            <span class="st-lbl">All</span>
            <span class="st-sub">Every submission ever sent</span>
        </a>
    </div>

    {{-- A real GET form, so search works with JavaScript off and the query stays
         shareable. The JS layers instant filtering on top of it. --}}
    <form method="GET" action="{{ route('backend.verification.index') }}" class="filter-bar st-toolbar vf-toolbar"
          data-lf data-lf-noun="verification">
        <input type="hidden" name="status" value="{{ $status }}" data-lf-field>
        <input type="hidden" name="type" value="{{ $type }}" data-lf-field>

        <div class="field st-search-field">
            <label for="vfSearch">Search verifications</label>
            <div class="st-search">
                <i class="fas fa-magnifying-glass"></i>
                <input type="search" name="q" id="vfSearch" class="input" value="{{ $term }}"
                       placeholder="Member, email, phone or type" autocomplete="off"
                       data-lf-input aria-describedby="vfSearchHint">
                <button type="button" class="st-search-clear" data-lf-clear
                        aria-label="Clear the search box" @if ($term === '') hidden @endif>
                    <i class="fas fa-xmark"></i>
                </button>
            </div>
            <p class="hint st-hint" id="vfSearchHint" data-lf-hint>
                @if ($term !== '')
                    Showing {{ $totalRows }} {{ Str::plural('verification', $totalRows) }} matching &ldquo;{{ $term }}&rdquo;.
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

    {{-- Type is the second facet, so it lives in its own chip row rather than a
         dropdown: both facets filter the same rows, in place, and each chip says
         how many it would show. Counts follow the active status, so a chip never
         promises rows the status filter has already excluded. --}}
    <div class="rp-chips vf-chips" role="group" aria-label="Filter by type">
        <span class="rp-chips-label">Type</span>
        <a class="rp-chip {{ $type === null ? 'is-active' : '' }}"
           href="{{ route('backend.verification.index', $withStatus(['type' => null, 'q' => $term ?: null])) }}"
           data-lf-tile data-lf-group="type"
           data-lf-key="all" data-lf-field="type" data-lf-label="any type"
           @if ($type === null) aria-current="true" @endif>
            All types
        </a>
        @foreach ($types as $key => $label)
            <a class="rp-chip {{ $type === $key ? 'is-active' : '' }}"
               href="{{ route('backend.verification.index', $withStatus(['type' => $key, 'q' => $term ?: null])) }}"
               data-lf-tile data-lf-group="type"
               data-lf-key="{{ $key }}" data-lf-field="type"
               data-lf-label="{{ mb_strtolower(Verification::TYPES_SHORT[$key] ?? $label) }}"
               data-lf-col="type" data-lf-val="{{ $key }}"
               data-lf-counts-for="status"
               data-lf-counts="{{ json_encode($typeCountsByStatus ? array_map(fn ($m) => $m[$key] ?? 0, $typeCountsByStatus) : []) }}"
               @if ($type === $key) aria-current="true" @endif>
                {{ $label }}
                <span class="rp-chip-n" data-lf-tile-count>{{ $typeCounts[$key] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <div class="st-result-bar">
        <span data-lf-count data-lf-total="{{ $totalRows }}">
            @if ($term !== '' || $type)
                {{ $totalRows }} {{ Str::plural('match', $totalRows) }}
            @else
                {{ $totalRows }} {{ Str::plural('verification', $totalRows) }}
            @endif
        </span>
        @if ($verifications->hasPages())
            <span class="text-muted text-small">Page {{ $verifications->currentPage() }} of {{ $verifications->lastPage() }}</span>
        @endif
    </div>

    <div class="table-wrap st-table-wrap">
        <table class="table st-table vf-table">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Type</th>
                    <th>Document</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th class="st-actions-col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($verifications as $v)
                    <tr data-lf-row
                        data-status="{{ $v->status }}"
                        data-type="{{ $v->type }}"
                        data-search="{{ $rowSearch($v) }}">
                        <td data-label="Member">
                            <div class="cell-user st-cell">
                                <span class="avatar st-thumb">
                                    @if ($v->user?->primaryPhoto)
                                        <img src="{{ $v->user->primaryPhoto->url() }}" alt="" loading="lazy">
                                    @else
                                        <span class="initials">{{ $v->user?->initials ?? 'J' }}</span>
                                    @endif
                                </span>
                                <div class="st-cell-text">
                                    <div class="cu-name st-title" data-hl>
                                        <a href="{{ route('backend.verification.show', $v) }}">{{ $v->user?->name ?? 'Deleted user' }}</a>
                                    </div>
                                    <div class="cu-sub" data-hl>{{ $v->user?->email ?? 'No longer on Jibon Sathi' }}</div>
                                    @if ($v->user?->phone)
                                        <div class="cu-sub" data-hl>{{ $v->user->phone }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td data-label="Type">
                            <span class="badge badge-info vf-type">
                                <i class="fas {{ $v->typeIcon() }}"></i>
                                {{ $v->typeShortLabel() }}
                            </span>
                        </td>
                        <td data-label="Document">
                            @if ($v->hasDocument())
                                <span class="vf-doc">
                                    <i class="fas fa-file-lines"></i>
                                    {{ $v->documentExtension() ?? 'FILE' }}
                                </span>
                                @if ($v->document_type)
                                    <div class="vf-doc-sub" data-hl>{{ $v->document_type }}</div>
                                @endif
                            @else
                                {{-- A submission with no file is normal for an email
                                     or phone check, so it is labelled, not an error. --}}
                                <span class="vf-doc vf-doc-none">
                                    <i class="fas fa-circle-minus"></i>
                                    Not needed
                                </span>
                            @endif
                        </td>
                        <td data-label="Status">
                            <span class="badge badge-{{ $v->statusTone() }}"><i class="fas fa-circle"></i> {{ $v->statusLabel() }}</span>
                            @if ($v->reviewer)
                                <div class="vf-handler">by {{ $v->reviewer->name }}</div>
                            @endif
                        </td>
                        <td data-label="Submitted">
                            <div class="st-meta">
                                <span class="st-meta-row">
                                    <i class="fas fa-clock"></i>
                                    {{ $v->created_at?->diffForHumans() ?? '—' }}
                                </span>
                                <span class="st-meta-row">
                                    <i class="fas fa-calendar"></i>
                                    {{ $v->created_at?->format('d M Y') ?? '—' }}
                                </span>
                            </div>
                        </td>
                        <td data-label="Actions" class="st-actions-col">
                            <div class="st-actions">
                                <a href="{{ route('backend.verification.show', $v) }}" class="st-act"
                                   title="Review case" aria-label="Review {{ $v->user?->name ?? 'this verification' }}&rsquo;s document">
                                    <i class="fas fa-gavel"></i>
                                </a>
                                @if ($v->hasDocument())
                                    <a href="{{ route('backend.verification.document', $v) }}" class="st-act" target="_blank" rel="noopener"
                                       title="Open document" aria-label="Open the uploaded document in a new tab">
                                        <i class="fas fa-file-arrow-up"></i>
                                    </a>
                                @endif
                                @if ($v->user)
                                    <a href="{{ route('backend.users.show', $v->user) }}" class="st-act"
                                       title="Open {{ $v->user->name }}" aria-label="Open the profile of {{ $v->user->name }}">
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
                                @if ($term !== '' || $type || $status !== 'pending')
                                    <h3>Nothing matches this view</h3>
                                    <p>No verification matches the current search and filter combination.</p>
                                    <a href="{{ route('backend.verification.index') }}" class="btn btn-outline">
                                        <i class="fas fa-rotate-left"></i> Reset filters
                                    </a>
                                @else
                                    <h3>The queue is clear</h3>
                                    <p>No identity documents are waiting for a decision. Anything sent later will land here.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Shown by the filter when the current term and chips hide every row on
             this page. Other pages may still hold matches. --}}
        <div class="empty-state st-live-empty vf-live-empty" data-lf-empty hidden>
            <i class="fas fa-magnifying-glass" data-lf-empty-icon></i>
            <h3 data-lf-empty-title>Nothing on this page matches</h3>
            <p data-lf-empty-text>Verifications on other pages may still match. Press Enter to search every page.</p>
            <a href="{{ route('backend.verification.index') }}" class="btn btn-outline" data-lf-empty-link hidden>
                Search every page
            </a>
        </div>
    </div>

    {{ $verifications->links('backend.components.pagination') }}
@endsection
