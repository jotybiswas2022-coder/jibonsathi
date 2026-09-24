@extends('frontend.layouts.member')

@section('title', 'Reported Profiles')

@section('member-content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Your Reports</h1>
            <p class="page-sub">Track the reports you've submitted to our moderation team.</p>
        </div>
        <a href="{{ route('discover.index') }}" class="btn btn-primary"><i class="fas fa-search"></i> Discover</a>
    </div>

    @if ($reports->isEmpty())
        <x-frontend::empty-state :icon="'fa-flag'" :title="'No reports submitted'"
            :description="'If you ever come across something inappropriate, you can report a profile from its detail page.'" />
    @else
        <div class="card">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Profile</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reports as $report)
                            <tr>
                                <td>
                                    <a href="{{ route('profiles.show', $report->reportedUser) }}" style="display:flex;align-items:center;gap:10px;color:var(--text)">
                                        <x-frontend::avatar :user="$report->reportedUser" :size="36" />
                                        <strong>{{ $report->reportedUser->name }}</strong>
                                    </a>
                                </td>
                                <td>{{ $report->reasonLabel() }}</td>
                                <td>
                                    <x-frontend::badge :tone="match ($report->status) {
                                        'resolved' => 'success',
                                        'dismissed' => 'muted',
                                        'investigating' => 'info',
                                        default => 'warning',
                                    }">{{ $report->status }}</x-frontend::badge>
                                </td>
                                <td class="text-muted">{{ $report->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">{{ $reports->links('frontend.components.pagination') }}</div>
    @endif
@endsection