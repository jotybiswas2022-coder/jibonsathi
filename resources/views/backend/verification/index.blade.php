@extends('backend.layouts.app')

@section('title', 'Identity Verifications')
@section('crumb', 'Review uploaded identity documents')

@section('content')
    <div class="tabs">
        @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label)
            <a class="tab {{ ($status ?? 'pending') === $key ? 'active' : '' }}" href="{{ route('backend.verification.index', ['status' => $key]) }}">
                {{ $label }}
                @isset($counts[$key]) <span class="tab-count">{{ $counts[$key] }}</span> @endisset
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('backend.verification.index') }}" class="filter-bar">
        <input type="hidden" name="status" value="{{ $status ?? 'pending' }}">
        <div class="field">
            <label>Type</label>
            <select name="type" class="input">
                <option value="">All types</option>
                @foreach (['email' => 'Email', 'phone' => 'Phone', 'profile' => 'Profile'] as $k => $l)
                    <option value="{{ $k }}" @selected(($type ?? null) === $k)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="field actions">
            <button class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('backend.verification.index') }}" class="btn btn-outline">Reset</a>
        </div>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($verifications as $v)
                    <tr>
                        <td>
                            <div class="cell-user">
                                <span class="avatar avatar-sm"><img src="{{ $v->user?->primaryPhoto?->url() ?? \App\Support\Media::avatar($v->user?->name ?? 'Jibon Sathi') }}" alt="" onerror="this.style.display='none'"><span class="initials">{{ $v->user?->initials ?? 'J' }}</span></span>
                                <div>
                                    <div class="cu-name"><a href="{{ route('backend.verification.show', $v) }}">{{ $v->user?->name ?? '—' }}</a></div>
                                    <div class="cu-sub">{{ $v->user?->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $v->type)) }}</span></td>
                        <td><span class="badge badge-{{ $v->status === 'approved' ? 'success' : ($v->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($v->status) }}</span></td>
                        <td class="text-muted">{{ $v->created_at?->format('d M Y, g:i A') }}</td>
                        <td style="text-align:right">
                            <a href="{{ route('backend.verification.show', $v) }}" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i> Review</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <i class="fas fa-shield-halved"></i>
                                <h3>No verifications</h3>
                                <p>Nothing matches this queue.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $verifications->links('backend.components.pagination') }}
@endsection