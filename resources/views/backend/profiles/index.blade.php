@extends('backend.layouts.app')

@section('title', 'Profiles')
@section('crumb', 'Profile moderation queue')

@section('content')
    <div class="tabs">
        @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'suspended' => 'Suspended'] as $key => $label)
            <a class="tab {{ ($filters['status'] ?? 'pending') === $key ? 'active' : '' }}" href="{{ route('backend.profiles.index', ['status' => $key]) }}">
                {{ $label }} <span class="tab-count">{{ $counts[$key] }}</span>
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('backend.profiles.index') }}" class="filter-bar">
        <input type="hidden" name="status" value="{{ $filters['status'] ?? 'pending' }}">
        <div class="field">
            <label>Search</label>
            <input type="search" name="q" class="input" value="{{ $filters['q'] ?? '' }}" placeholder="Name or email">
        </div>
        <div class="field">
            <label>Completion</label>
            <select name="completion" class="input">
                <option value="">All</option>
                <option value="low" @selected(($filters['completion'] ?? null) === 'low')>&lt; 60%</option>
                <option value="high" @selected(($filters['completion'] ?? null) === 'high')>&ge; 90%</option>
            </select>
        </div>
        <div class="field actions">
            <button class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('backend.profiles.index') }}" class="btn btn-outline">Reset</a>
        </div>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Status</th>
                    <th>Completion</th>
                    <th>Verified</th>
                    <th>Submitted</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($profiles as $p)
                    <tr>
                        <td>
                            <div class="cell-user">
                                <span class="avatar avatar-sm"><img src="{{ $p->user?->primaryPhoto?->url() ?? \App\Support\Media::avatar($p->user?->name ?? 'Jibon Sathi') }}" alt="" onerror="this.style.display='none'"><span class="initials">{{ $p->user?->initials ?? 'J' }}</span></span>
                                <div>
                                    <div class="cu-name"><a href="{{ route('backend.profiles.show', $p) }}">{{ $p->user?->name ?? '—' }}</a></div>
                                    <div class="cu-sub">{{ $p->user?->email }} · {{ $p->user?->phone }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge badge-{{ $p->profile_status === 'approved' ? 'success' : ($p->profile_status === 'rejected' ? 'danger' : ($p->profile_status === 'suspended' ? 'danger' : 'warning')) }}">{{ ucfirst($p->profile_status) }}</span></td>
                        <td>
                            <div class="progress" style="width:110px"><span style="width:{{ $p->profile_completion }}%"></span></div>
                            <span class="text-tiny text-muted">{{ $p->profile_completion }}%</span>
                        </td>
                        <td>
                            @if ($p->verification_status === 'verified')
                                <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Verified</span>
                            @else
                                <span class="badge badge-muted">{{ ucfirst(str_replace('_',' ',$p->verification_status)) }}</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $p->created_at?->format('d M Y') }}</td>
                        <td style="text-align:right">
                            <a href="{{ route('backend.profiles.show', $p) }}" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i> Review</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="fas fa-id-card"></i>
                                <h3>Nothing here</h3>
                                <p>No profiles match this queue.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $profiles->links('backend.components.pagination') }}
@endsection