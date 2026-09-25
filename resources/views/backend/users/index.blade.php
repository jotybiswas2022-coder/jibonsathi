@extends('backend.layouts.app')

@php
    use App\Support\Reference;
    $statusBadge = ['active' => 'badge-success', 'pending' => 'badge-warning', 'suspended' => 'badge-danger', 'inactive' => 'badge-muted'];
@endphp

@section('title', 'Members')
@section('crumb', 'All members · filter and manage accounts')

@section('content')
    <form method="GET" action="{{ route('backend.users.index') }}" class="filter-bar">
        <div class="field">
            <label>Search</label>
            <input type="search" name="q" class="input" value="{{ $filters['q'] ?? '' }}" placeholder="Name or email">
        </div>
        <div class="field">
            <label>Status</label>
            <select name="status" class="input">
                <option value="">All statuses</option>
                @foreach (Reference::userStatuses() as $key => $label)
                    <option value="{{ $key }}" @selected(($filters['status'] ?? null) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label>Gender</label>
            <select name="gender" class="input">
                <option value="">All</option>
                @foreach (Reference::genders() as $key => $label)
                    <option value="{{ $key }}" @selected(($filters['gender'] ?? null) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label>Verified</label>
            <select name="verified" class="input">
                <option value="">All</option>
                <option value="1" @selected(! empty($filters['verified']))>Verified only</option>
            </select>
        </div>
        <div class="field">
            <label>Role</label>
            <select name="role" class="input">
                <option value="">All</option>
                <option value="admin" @selected(($filters['role'] ?? null) === 'admin')>Admins</option>
                <option value="member" @selected(($filters['role'] ?? null) === 'member')>Members</option>
            </select>
        </div>
        <div class="field">
            <label>Sort</label>
            <select name="sort" class="input">
                <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Newest first</option>
                <option value="oldest" @selected(($filters['sort'] ?? null) === 'oldest')>Oldest first</option>
                <option value="name" @selected(($filters['sort'] ?? null) === 'name')>Name A–Z</option>
            </select>
        </div>
        <div class="field actions">
            <button class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('backend.users.index') }}" class="btn btn-outline">Reset</a>
        </div>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Status</th>
                    <th>Gender</th>
                    <th>Verified</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $u)
                    <tr>
                        <td>
                            <div class="cell-user">
                                <span class="avatar avatar-sm"><img src="{{ $u->primaryPhoto?->url() ?? \App\Support\Media::avatar($u->name) }}" alt="" onerror="this.style.display='none'"><span class="initials">{{ $u->initials }}</span></span>
                                <div>
                                    <div class="cu-name">
                                        <a href="{{ route('backend.users.show', $u) }}">{{ $u->name }}</a>
                                        @if ($u->is_admin)
                                            <i class="fas fa-crown admin-mark" title="Admin" aria-label="Administrator"></i>
                                        @endif
                                    </div>
                                    <div class="cu-sub">{{ $u->email }} · {{ $u->phone }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge {{ $statusBadge[$u->status] ?? 'badge-muted' }}">{{ ucfirst($u->status) }}</span></td>
                        <td class="text-muted">{{ $u->profile?->gender ?? '—' }}</td>
                        <td>
                            @if (($u->profile?->verification_status ?? '') === 'verified')
                                <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Verified</span>
                            @else
                                <span class="badge badge-muted">No</span>
                            @endif
                        </td>
                        <td>{{ $u->is_admin ? 'Admin' : 'Member' }}</td>
                        <td class="text-muted">{{ $u->created_at?->format('d M Y') }}</td>
                        <td style="text-align:right">
                            <a href="{{ route('backend.users.edit', $u) }}" class="btn btn-soft btn-sm"><i class="fas fa-pen"></i> Edit</a>
                            <a href="{{ route('backend.users.show', $u) }}" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i> View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-users-slash"></i>
                                <h3>No members found</h3>
                                <p>Try adjusting your filters or search term.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->links('backend.components.pagination') }}
@endsection