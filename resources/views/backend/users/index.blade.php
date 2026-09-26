@extends('backend.layouts.app')

@php
    use App\Support\Reference;

    $totalRows = $users->total();

    $statusBadge = ['active' => 'badge-success', 'pending' => 'badge-warning', 'suspended' => 'badge-danger', 'inactive' => 'badge-muted'];

    /* The tiles are the status filter the old select used to be, so the most
       common jobs — who can sign in, who is waiting, who is blocked — are one
       click away instead of a form submit. */
    $tiles = [
        ['key' => 'active', 'label' => 'Active', 'icon' => 'fa-user-check', 'tone' => 'ic-success',
            'sub' => 'Can sign in and appear in search'],
        ['key' => 'pending', 'label' => 'Pending', 'icon' => 'fa-hourglass-half', 'tone' => 'ic-warning',
            'sub' => 'Waiting for approval'],
        ['key' => 'suspended', 'label' => 'Suspended', 'icon' => 'fa-ban', 'tone' => 'ic-danger',
            'sub' => 'Blocked from the site'],
        ['key' => 'inactive', 'label' => 'Inactive', 'icon' => 'fa-user-slash', 'tone' => 'ic-muted',
            'sub' => 'Deactivated accounts'],
    ];

    /* Every other facet rides along on the tile links, so switching queue never
       silently drops a search term or a role filter. */
    $keep = collect($filters)->except(['page', 'status'])->all();
    $tileUrl = fn (?string $s): string => route('backend.users.index', array_filter(
        array_merge($keep, ['status' => $s]),
        fn ($v) => $v !== null && $v !== ''
    ));

    $term = $filters['q'] ?? '';
    $isFiltered = $term !== '' || $status !== null;
@endphp

@section('title', 'Members')
@section('crumb', 'All members · filter and manage accounts')

@section('content')
    <div class="page-head">
        <div class="page-head-text">
            <h2>Members</h2>
            <p>Every account on Jibon Sathi and where it stands. Filter to a status, search a name or email, then open an account to verify, suspend or edit it.</p>
        </div>
        <div class="page-head-actions">
            <a href="{{ $tileUrl(null) }}" class="btn btn-outline {{ $status === null ? 'is-active' : '' }}"
               @if ($status === null && $term === '') aria-disabled="true" tabindex="-1" style="pointer-events:none;opacity:.5" @endif
               @if ($status === null) aria-current="true" @endif>
                <i class="fas fa-layer-group"></i> All members
            </a>
        </div>
    </div>

    @if (($counts['pending'] ?? 0) > 0 && $status !== 'pending')
        <div class="alert alert-warning uf-nudge">
            <i class="fas fa-hourglass-half"></i>
            <div>
                <strong>{{ $counts['pending'] }} {{ Str::plural('account', $counts['pending']) }} still {{ $counts['pending'] === 1 ? 'needs' : 'need' }} approval.</strong>
                <a href="{{ $tileUrl('pending') }}">Go to the pending accounts</a>
            </div>
        </div>
    @endif

    <div class="stat-grid st-tiles">
        @foreach ($tiles as $tile)
            @php $isActive = $status === $tile['key']; @endphp
            <a class="card stat-tile st-tile {{ $isActive ? 'is-active' : '' }}"
               href="{{ $tileUrl($tile['key']) }}"
               @if ($isActive) aria-current="true" @endif>
                <span class="st-ico {{ $tile['tone'] }}"><i class="fas {{ $tile['icon'] }}"></i></span>
                <span class="st-num">{{ number_format($counts[$tile['key']] ?? 0) }}</span>
                <span class="st-lbl">{{ $tile['label'] }}</span>
                <span class="st-sub">{{ $tile['sub'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- A real GET form, so every filter works with JavaScript off and the query
         stays shareable. The status comes from the tiles, so it rides here as a
         hidden field rather than a fifth dropdown. --}}
    <form method="GET" action="{{ route('backend.users.index') }}" class="filter-bar st-toolbar uf-toolbar">
        <input type="hidden" name="status" value="{{ $status }}">

        <div class="field st-search-field">
            <label for="ufSearch">Search members</label>
            <div class="st-search">
                <i class="fas fa-magnifying-glass"></i>
                <input type="search" name="q" id="ufSearch" class="input" value="{{ $term }}"
                       placeholder="Name, email or phone" autocomplete="off">
            </div>
        </div>

        <div class="field">
            <label for="ufRole">Role</label>
            <select name="role" id="ufRole" class="input">
                <option value="">All roles</option>
                <option value="member" @selected(($filters['role'] ?? null) === 'member')>Members</option>
                <option value="admin" @selected(($filters['role'] ?? null) === 'admin')>Admins</option>
            </select>
        </div>

        <div class="field">
            <label for="ufVerified">Identity</label>
            <select name="verified" id="ufVerified" class="input">
                <option value="">Any identity</option>
                <option value="1" @selected(! empty($filters['verified']))>Verified only</option>
            </select>
        </div>

        <div class="field">
            <label for="ufGender">Gender</label>
            <select name="gender" id="ufGender" class="input">
                <option value="">All genders</option>
                @foreach (Reference::genders() as $key => $label)
                    <option value="{{ $key }}" @selected(($filters['gender'] ?? null) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="ufSort">Sort</label>
            <select name="sort" id="ufSort" class="input">
                <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Newest first</option>
                <option value="oldest" @selected(($filters['sort'] ?? null) === 'oldest')>Oldest first</option>
                <option value="name" @selected(($filters['sort'] ?? null) === 'name')>Name A–Z</option>
            </select>
        </div>

        <div class="field actions">
            <button class="btn btn-primary"><i class="fas fa-filter"></i> Apply</button>
            <a href="{{ route('backend.users.index') }}" class="btn btn-outline">Reset</a>
        </div>
    </form>

    <div class="st-result-bar">
        <span>
            @if ($isFiltered)
                {{ $totalRows }} {{ Str::plural('match', $totalRows) }}
            @else
                {{ $totalRows }} {{ Str::plural('member', $totalRows) }}
            @endif
        </span>
        @if ($users->hasPages())
            <span class="text-muted text-small">Page {{ $users->currentPage() }} of {{ $users->lastPage() }}</span>
        @endif
    </div>

    <div class="table-wrap st-table-wrap">
        <table class="table st-table uf-table">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Status</th>
                    <th>Identity</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th class="st-actions-col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $u)
                    <tr>
                        <td data-label="Member">
                            <div class="cell-user st-cell">
                                <span class="avatar st-thumb">
                                    <img src="{{ $u->primaryPhoto?->url() ?? \App\Support\Media::avatar($u->name) }}" alt="" loading="lazy" onerror="this.style.display='none'">
                                    <span class="initials">{{ $u->initials }}</span>
                                </span>
                                <div class="st-cell-text">
                                    <div class="cu-name st-title">
                                        <a href="{{ route('backend.users.show', $u) }}">
                                            {{ $u->name }}
                                            @if ($u->is_admin)
                                                <i class="fas fa-crown admin-mark" title="Admin" aria-label="Administrator"></i>
                                            @endif
                                        </a>
                                    </div>
                                    <div class="cu-sub">{{ $u->email }}</div>
                                    <div class="cu-sub">{{ $u->phone ?: 'No phone on file' }}</div>
                                </div>
                            </div>
                        </td>
                        <td data-label="Status">
                            <span class="badge {{ $statusBadge[$u->status] ?? 'badge-muted' }}"><i class="fas fa-circle"></i> {{ ucfirst($u->status) }}</span>
                        </td>
                        <td data-label="Identity">
                            @if (($u->profile?->verification_status ?? '') === 'verified')
                                <span class="badge badge-success"><i class="fas fa-circle-check"></i> Verified</span>
                            @else
                                <span class="badge badge-muted">Not verified</span>
                            @endif
                        </td>
                        <td data-label="Role">
                            @if ($u->is_admin)
                                <span class="badge badge-accent"><i class="fas fa-crown"></i> Admin</span>
                            @else
                                <span class="badge badge-info">Member</span>
                            @endif
                        </td>
                        <td data-label="Joined">
                            <div class="st-meta">
                                <span class="st-meta-row">
                                    <i class="fas fa-calendar"></i>
                                    {{ $u->created_at?->format('d M Y') ?? '—' }}
                                </span>
                                <span class="st-meta-row">
                                    <i class="fas fa-clock"></i>
                                    {{ $u->created_at?->diffForHumans() ?? '—' }}
                                </span>
                            </div>
                        </td>
                        <td data-label="Actions" class="st-actions-col">
                            <div class="st-actions">
                                <a href="{{ route('backend.users.show', $u) }}" class="st-act"
                                   title="Open account" aria-label="Open the account of {{ $u->name }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('backend.users.edit', $u) }}" class="st-act"
                                   title="Edit account" aria-label="Edit {{ $u->name }}">
                                    <i class="fas fa-pen"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="fas fa-users-slash"></i>
                                @if ($isFiltered)
                                    <h3>Nothing matches this view</h3>
                                    <p>No member matches the current search and filter combination.</p>
                                    <a href="{{ route('backend.users.index') }}" class="btn btn-outline">
                                        <i class="fas fa-rotate-left"></i> Reset filters
                                    </a>
                                @else
                                    <h3>No members yet</h3>
                                    <p>Accounts will appear here as soon as people sign up.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->links('backend.components.pagination') }}
@endsection
