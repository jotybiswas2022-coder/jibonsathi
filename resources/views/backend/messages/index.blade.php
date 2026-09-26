@extends('backend.layouts.app')

@php
    $onPage = $conversations->count();
    $totalRows = $conversations->total();

    /* The tiles double as the scope filter, which is what the old "Only
       flagged" dropdown used to do on its own. "col"/"val" say which row
       attribute the shared filter matches that tile on, and are left off for
       the tile that means "no narrowing". */
    $scopes = [
        ['key' => 'all', 'label' => 'All threads', 'icon' => 'fa-layer-group', 'tone' => 'ic-brand',
            'sub' => 'Every conversation on file', 'col' => null, 'val' => null],
        ['key' => 'flagged', 'label' => 'Flagged', 'icon' => 'fa-flag', 'tone' => 'ic-danger',
            'sub' => 'A member has an open report', 'col' => 'scope', 'val' => 'flagged'],
        ['key' => 'week', 'label' => 'Active this week', 'icon' => 'fa-clock-rotate-left', 'tone' => 'ic-success',
            'sub' => 'Replied in the last 7 days', 'col' => 'week', 'val' => '1'],
        ['key' => 'empty', 'label' => 'No messages', 'icon' => 'fa-comment-slash', 'tone' => 'ic-warning',
            'sub' => 'Threads that never started', 'col' => 'empty', 'val' => '1'],
    ];

    /* One string per row, so the box can filter without another request. */
    $rowSearch = static function (\App\Models\Conversation $c): string {
        return mb_strtolower(trim(implode(' ', array_filter([
            $c->userOne?->name,
            $c->userOne?->email,
            $c->userTwo?->name,
            $c->userTwo?->email,
            $c->latestMessage?->body,
        ]))));
    };
@endphp

@section('title', 'Conversations')
@section('crumb', 'Oversight only · admins never join member chats')

@section('content')
    <div class="page-head">
        <div class="page-head-text">
            <h2>Conversations</h2>
            <p>Read-only oversight of member chats. Nothing here can be replied to, so a thread always stays exactly as the two members left it.</p>
        </div>
        <div class="page-head-actions">
            <span class="badge badge-muted"><i class="fas fa-eye"></i> Read only</span>
        </div>
    </div>

    {{-- The tiles are the filter, so the old "Only flagged" dropdown is gone.
         Each href is a real query, so the tiles still work with JavaScript off.
         data-lf-col says which row attribute the tile matches on, which is how
         one shared filter drives four different scopes. --}}
    <div class="stat-grid st-tiles ms-tiles">
        @foreach ($scopes as $tile)
            @php $isActive = $scope === $tile['key']; @endphp
            <a class="card stat-tile st-tile {{ $isActive ? 'is-active' : '' }}"
               href="{{ route('backend.messages.index', array_filter(['scope' => $tile['key'] === 'all' ? null : $tile['key'], 'q' => $term ?: null])) }}"
               data-lf-tile data-lf-group="scope"
               data-lf-key="{{ $tile['key'] }}" data-lf-field="scope"
               data-lf-label="{{ mb_strtolower($tile['label']) }}"
               @if ($tile['col']) data-lf-col="{{ $tile['col'] }}" data-lf-val="{{ $tile['val'] }}" @endif
               @if ($isActive) aria-current="true" @endif>
                <span class="st-ico {{ $tile['tone'] }}"><i class="fas {{ $tile['icon'] }}"></i></span>
                <span class="st-num" data-lf-tile-count>{{ $counts[$tile['key']] ?? 0 }}</span>
                <span class="st-lbl">{{ $tile['label'] }}</span>
                <span class="st-sub">{{ $tile['sub'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- A real GET form, so search works with JavaScript off and the query
         stays shareable. The JS layers instant filtering on top. --}}
    <form method="GET" action="{{ route('backend.messages.index') }}" class="filter-bar st-toolbar ms-toolbar"
          data-lf data-lf-noun="thread" data-lf-noun-plural="threads">
        <input type="hidden" name="scope" value="{{ $scope }}" data-lf-field>

        <div class="field st-search-field">
            <label for="msSearch">Search conversations</label>
            <div class="st-search">
                <i class="fas fa-magnifying-glass"></i>
                <input type="search" name="q" id="msSearch" class="input" value="{{ $term }}"
                       placeholder="Member name, email or message" autocomplete="off"
                       data-lf-input aria-describedby="msSearchHint">
                <button type="button" class="st-search-clear" data-lf-clear
                        aria-label="Clear the search box" @if ($term === '') hidden @endif>
                    <i class="fas fa-xmark"></i>
                </button>
            </div>
            <p class="hint st-hint" id="msSearchHint" data-lf-hint>
                @if ($term !== '')
                    Showing {{ $totalRows }} {{ Str::plural('thread', $totalRows) }} matching &ldquo;{{ $term }}&rdquo;.
                @else
                    Type to filter the rows below, or press Enter to search every page.
                @endif
            </p>
        </div>

        {{-- The box filters as you type, so there is nothing to press. The
             hidden submit keeps Enter working and makes the form usable
             without JavaScript. --}}
        <button type="submit" class="sr-only" tabindex="-1" aria-hidden="true">Search</button>
    </form>

    <div class="st-result-bar">
        <span data-lf-count data-lf-total="{{ $totalRows }}">
            @if ($term !== '')
                {{ $totalRows }} {{ Str::plural('match', $totalRows) }}
            @else
                {{ $totalRows }} {{ Str::plural('thread', $totalRows) }}
            @endif
        </span>
        @if ($conversations->hasPages())
            <span class="text-muted text-small">Page {{ $conversations->currentPage() }} of {{ $conversations->lastPage() }}</span>
        @endif
    </div>

    <div class="table-wrap st-table-wrap">
        <table class="table st-table ms-table">
            <thead>
                <tr>
                    <th>Members</th>
                    <th>Last message</th>
                    <th>Activity</th>
                    <th class="ms-flags-col">Status</th>
                    <th class="st-actions-col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($conversations as $c)
                    @php
                        $reports = $c->openReportCounts();
                        $one = $c->userOne;
                        $two = $c->userTwo;
                        $last = $c->latestMessage;
                    @endphp
                    <tr data-lf-row
                        data-scope="{{ $c->hasOpenReports() ? 'flagged' : 'all' }}"
                        data-empty="{{ $c->messages_count === 0 ? '1' : '0' }}"
                        data-week="{{ $c->last_message_at && $c->last_message_at->gte(now()->subDays(7)) ? '1' : '0' }}"
                        data-search="{{ $rowSearch($c) }}">
                        <td data-label="Members">
                            <div class="cell-user ms-cell">
                                <span class="ms-avatars">
                                    @foreach ([$one, $two] as $member)
                                        <span class="avatar ms-avatar" title="{{ $member?->name ?? 'Deleted member' }}">
                                            @if ($member?->primaryPhoto)
                                                <img src="{{ $member->primaryPhoto->url() }}" alt="" loading="lazy">
                                            @else
                                                <span class="initials">{{ $member?->initials ?? 'J' }}</span>
                                            @endif
                                        </span>
                                    @endforeach
                                </span>
                                <div class="st-cell-text">
                                    <div class="cu-name st-title" data-hl>
                                        <a href="{{ route('backend.messages.show', $c) }}">{{ $c->participantsLabel() }}</a>
                                    </div>
                                    <div class="cu-sub" data-hl>
                                        {{ $one?->email ?? 'No email' }} · {{ $two?->email ?? 'No email' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td data-label="Last message">
                            @if ($last)
                                <div class="ms-preview" data-hl>
                                    <span class="ms-preview-who">{{ $last->sender?->name ?? 'Deleted member' }}</span>
                                    <span class="ms-preview-body">{{ $last->body }}</span>
                                </div>
                            @else
                                <span class="text-muted">No messages yet</span>
                            @endif
                        </td>
                        <td data-label="Activity">
                            <div class="st-meta">
                                <span class="st-meta-row" data-hl>
                                    <i class="fas fa-clock"></i>
                                    {{ $c->last_message_at?->diffForHumans() ?? 'Never' }}
                                </span>
                                <span class="st-meta-row">
                                    <i class="fas fa-comment"></i>
                                    {{ $c->messages_count }} {{ Str::plural('message', $c->messages_count) }}
                                </span>
                            </div>
                        </td>
                        <td data-label="Status" class="ms-flags-col">
                            @if ($c->hasOpenReports())
                                <span class="badge badge-danger"><i class="fas fa-flag"></i> Flagged</span>
                            @elseif ($c->messages_count === 0)
                                <span class="badge badge-muted">Empty</span>
                            @else
                                <span class="badge badge-success"><i class="fas fa-circle-check"></i> Clear</span>
                            @endif
                            @if ($reports['one'] > 0 || $reports['two'] > 0)
                                <div class="ms-report-note">
                                    {{ $reports['one'] + $reports['two'] }} open
                                    {{ Str::plural('report', $reports['one'] + $reports['two']) }}
                                </div>
                            @endif
                        </td>
                        <td data-label="Actions" class="st-actions-col">
                            <div class="st-actions">
                                <a href="{{ route('backend.messages.show', $c) }}" class="st-act"
                                   title="Open thread" aria-label="Open the thread between {{ $c->participantsLabel() }}">
                                    <i class="fas fa-comments"></i>
                                </a>
                                @if ($one)
                                    <a href="{{ route('backend.users.show', $one) }}" class="st-act"
                                       title="Open {{ $one->name }}" aria-label="Open the profile of {{ $one->name }}">
                                        <i class="fas fa-user"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <i class="fas fa-comments"></i>
                                @if ($term !== '' || $scope !== 'all')
                                    <h3>Nothing matches this view</h3>
                                    <p>No conversation matches the current search and filter combination.</p>
                                    <a href="{{ route('backend.messages.index') }}" class="btn btn-outline">
                                        <i class="fas fa-rotate-left"></i> Reset filters
                                    </a>
                                @else
                                    <h3>No conversations yet</h3>
                                    <p>Threads appear here as soon as two members message each other.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Shown by the filter when the current term and scope hide every row
             on this page. Other pages may still hold matches. --}}
        <div class="empty-state st-live-empty ms-live-empty" data-lf-empty hidden>
            <i class="fas fa-magnifying-glass" data-lf-empty-icon></i>
            <h3 data-lf-empty-title>Nothing on this page matches</h3>
            <p data-lf-empty-text>Conversations on other pages may still match. Press Enter to search every page.</p>
            <a href="{{ route('backend.messages.index') }}" class="btn btn-outline" data-lf-empty-link hidden>
                Search every page
            </a>
        </div>
    </div>

    {{ $conversations->links('backend.components.pagination') }}
@endsection
