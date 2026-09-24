@extends('backend.layouts.app')

@section('title', 'Reports')
@section('crumb', 'Member complaint triage')

@section('content')
    <div class="tabs">
        @foreach (['pending' => 'Pending', 'investigating' => 'Investigating', 'resolved' => 'Resolved', 'dismissed' => 'Dismissed', 'all' => 'All'] as $key => $label)
            <a class="tab {{ ($status ?? 'pending') === $key ? 'active' : '' }}" href="{{ route('backend.reports.index', ['status' => $key]) }}">
                {{ $label }} <span class="tab-count">{{ $counts[$key] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('backend.reports.index') }}" class="filter-bar">
        <input type="hidden" name="status" value="{{ $status ?? 'pending' }}">
        <div class="field">
            <label>Reason</label>
            <select name="reason" class="input">
                <option value="">All reasons</option>
                @foreach ($reasons as $key => $label)
                    <option value="{{ $key }}" @selected(($reason ?? null) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="field actions">
            <button class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('backend.reports.index') }}" class="btn btn-outline">Reset</a>
        </div>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Reported Member</th>
                    <th>Reason</th>
                    <th>Reported By</th>
                    <th>Status</th>
                    <th>Reported</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reports as $r)
                    <tr>
                        <td>
                            <div class="cell-user">
                                <span class="avatar avatar-sm"><img src="{{ $r->reportedUser?->primaryPhoto?->url() ?? \App\Support\Media::avatar($r->reportedUser?->name ?? 'Jibon Sathi') }}" alt="" onerror="this.style.display='none'"><span class="initials">{{ $r->reportedUser?->initials ?? 'J' }}</span></span>
                                <div>
                                    <div class="cu-name"><a href="{{ route('backend.reports.show', $r) }}">{{ $r->reportedUser?->name ?? 'Deleted user' }}</a></div>
                                    <div class="cu-sub">{{ $r->reportedUser?->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge badge-danger">{{ $reasons[$r->reason] ?? $r->reason }}</span></td>
                        <td class="text-muted">{{ $r->reporter?->name ?? 'Member' }}</td>
                        <td><span class="badge badge-{{ $r->status === 'pending' ? 'warning' : ($r->status === 'resolved' ? 'success' : ($r->status === 'dismissed' ? 'muted' : 'info')) }}">{{ ucfirst($r->status) }}</span></td>
                        <td class="text-muted">{{ $r->created_at?->format('d M Y') }}</td>
                        <td style="text-align:right">
                            <a href="{{ route('backend.reports.show', $r) }}" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i> Review</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="fas fa-flag"></i>
                                <h3>All clear</h3>
                                <p>No reports match this queue.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $reports->links('backend.components.pagination') }}
@endsection