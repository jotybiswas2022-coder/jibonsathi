@extends('backend.layouts.app')

@section('title', 'Dashboard')
@section('crumb', 'Overview · Real-time platform health')

@section('content')
    <div class="page-head">
        <div class="page-head-text">
            <h2>Platform health</h2>
            <p>Members, profiles and open moderation work in one view. Clear the pending queues first — a verification or a report that waits is a member left in limbo.</p>
        </div>
        <div class="page-head-actions">
            <a href="{{ route('backend.verification.index', ['status' => 'pending']) }}" class="btn btn-outline">
                <i class="fas fa-shield-halved"></i> Review verifications ({{ number_format($counters['pending_verification']) }})
            </a>
            <a href="{{ route('backend.reports.index') }}" class="btn btn-outline">
                <i class="fas fa-flag"></i> Triage reports ({{ number_format($counters['reported_profiles']) }})
            </a>
        </div>
    </div>

    {{-- The tiles that lead somewhere are links; the rest stay read-only cards. --}}
    <div class="stat-grid st-tiles">
        @foreach ([
            ['lbl' => 'Total Members', 'num' => number_format($counters['total_users']), 'ico' => 'fa-users', 'cls' => 'ic-brand', 'sub' => 'All accounts', 'url' => route('backend.users.index')],
            ['lbl' => 'Active Members', 'num' => number_format($counters['active_users']), 'ico' => 'fa-user-check', 'cls' => 'ic-success', 'sub' => 'Status: active', 'url' => route('backend.users.index', ['status' => 'active'])],
            ['lbl' => 'Verified Profiles', 'num' => number_format($counters['verified_profiles']), 'ico' => 'fa-circle-check', 'cls' => 'ic-accent', 'sub' => 'Identity verified', 'url' => route('backend.profiles.index', ['status' => 'approved'])],
            ['lbl' => 'Pending Verification', 'num' => number_format($counters['pending_verification']), 'ico' => 'fa-shield-halved', 'cls' => 'ic-warning', 'sub' => 'Awaiting review', 'url' => route('backend.verification.index', ['status' => 'pending'])],
            ['lbl' => 'Open Reports', 'num' => number_format($counters['reported_profiles']), 'ico' => 'fa-flag', 'cls' => 'ic-danger', 'sub' => 'Pending + investigating', 'url' => route('backend.reports.index')],
            ['lbl' => 'New This Week', 'num' => number_format($counters['new_registrations']), 'ico' => 'fa-user-plus', 'cls' => 'ic-info', 'sub' => 'Last 7 days', 'url' => route('backend.users.index', ['sort' => 'newest'])],
            ['lbl' => 'Active Conversations', 'num' => number_format($counters['active_conversations']), 'ico' => 'fa-comments', 'cls' => 'ic-brand', 'sub' => 'Messaged in 7 days', 'url' => route('backend.messages.index')],
            ['lbl' => 'Profile Views', 'num' => number_format($counters['profile_views']), 'ico' => 'fa-eye', 'cls' => 'ic-muted', 'sub' => 'All time', 'url' => null],
        ] as $stat)
            @if ($stat['url'])
                <a href="{{ $stat['url'] }}" class="card stat-tile st-tile">
                    <span class="st-ico {{ $stat['cls'] }}"><i class="fas {{ $stat['ico'] }}"></i></span>
                    <span class="st-num">{{ $stat['num'] }}</span>
                    <span class="st-lbl">{{ $stat['lbl'] }}</span>
                    <span class="st-sub text-muted">{{ $stat['sub'] }}</span>
                </a>
            @else
                <div class="card stat-tile">
                    <span class="st-ico {{ $stat['cls'] }}"><i class="fas {{ $stat['ico'] }}"></i></span>
                    <span class="st-num">{{ $stat['num'] }}</span>
                    <span class="st-lbl">{{ $stat['lbl'] }}</span>
                    <span class="st-sub text-muted">{{ $stat['sub'] }}</span>
                </div>
            @endif
        @endforeach
    </div>

    @php
        $chartJson = fn (array $chart): string => json_encode($chart, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    @endphp

    <div class="chart-grid mt-4">
        <div class="card chart-card">
            <h4><i class="fas fa-chart-line" style="color:var(--brand)"></i> Member & Profile Growth</h4>
            <canvas data-chart="{{ $chartJson([
                'type' => 'line',
                'height' => 240,
                'labels' => $growth['labels'],
                'datasets' => [
                    ['label' => 'Members', 'data' => $growth['users'], 'color' => '#8B1E3F'],
                    ['label' => 'Profiles', 'data' => $growth['profiles'], 'color' => '#D4AF37'],
                ],
            ]) }}"></canvas>
        </div>
        <div class="card chart-card">
            <h4><i class="fas fa-location-dot" style="color:var(--brand)"></i> Top Locations</h4>
            <canvas data-chart="{{ $chartJson([
                'type' => 'bar',
                'height' => 240,
                'labels' => $locations['labels'],
                'datasets' => [['data' => $locations['values'], 'color' => '#8B1E3F']],
            ]) }}"></canvas>
        </div>
    </div>

    <div class="chart-grid mt-4">
        <div class="card chart-card">
            <h4><i class="fas fa-user-plus" style="color:var(--brand)"></i> Daily Registrations</h4>
            <canvas data-chart="{{ $chartJson([
                'type' => 'bar',
                'height' => 210,
                'labels' => $registrations['labels'],
                'datasets' => [['data' => $registrations['values'], 'color' => '#8B1E3F', 'color2' => '#D4AF37']],
            ]) }}"></canvas>
        </div>
        <div class="card chart-card">
            <h4><i class="fas fa-shield-halved" style="color:var(--brand)"></i> Verifications Decided</h4>
            <canvas data-chart="{{ $chartJson([
                'type' => 'line',
                'height' => 210,
                'labels' => $verifications['labels'],
                'datasets' => [['data' => $verifications['values'], 'color' => '#16A34A']],
            ]) }}"></canvas>
        </div>
        <div class="card chart-card">
            <h4><i class="fas fa-flag" style="color:var(--brand)"></i> Reports by Reason</h4>
            <canvas data-chart="{{ $chartJson([
                'type' => 'bar',
                'height' => 210,
                'labels' => $reasons['labels'],
                'datasets' => [['data' => $reasons['values'], 'color' => '#DC2626']],
            ]) }}"></canvas>
        </div>
    </div>

    <div class="dash-cols mt-4">
        <div class="card">
            <div class="card-head">
                <h3>Latest Members</h3>
                <a href="{{ route('backend.users.index') }}" class="text-small" style="font-weight:600">View all</a>
            </div>
            <div class="table-wrap st-table-wrap" style="border:0;box-shadow:none;border-radius:0">
                <table class="table st-table" style="min-width:0">
                    <tbody>
                        @foreach ($latestUsers as $u)
                            <tr>
                                <td data-label="Member">
                                    <div class="cell-user st-cell">
                                        <span class="avatar avatar-sm st-thumb"><img src="{{ $u->primaryPhoto?->url() ?? \App\Support\Media::avatar($u->name) }}" alt="" onerror="this.style.display='none'"><span class="initials">{{ $u->initials }}</span></span>
                                        <div class="st-cell-text">
                                            <div class="cu-name st-title"><a href="{{ route('backend.users.show', $u) }}">{{ $u->name }}</a></div>
                                            <div class="cu-sub">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Joined" class="text-muted">{{ $u->created_at?->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <h3>Pending Verifications</h3>
                <a href="{{ route('backend.verification.index') }}" class="text-small" style="font-weight:600">Review</a>
            </div>
            <div class="card-body" style="padding-top:8px">
                @forelse ($pendingVerifications as $item)
                    <div class="info-row">
                        <i class="fas fa-shield-halved"></i>
                        <div style="min-width:0">
                            <div class="ir-value">
                                <a href="{{ route('backend.verification.show', $item) }}">{{ $item->user?->name }}</a>
                            </div>
                            <div class="ir-label">{{ ucfirst(str_replace('_', ' ', $item->type)) }} · {{ $item->created_at?->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state" style="padding:24px 8px">
                        <i class="fa-regular fa-circle-check" style="color:var(--success)"></i>
                        <h3>All clear</h3>
                        <p>No identity verifications waiting for review.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <h3>Open Reports</h3>
                <a href="{{ route('backend.reports.index') }}" class="text-small" style="font-weight:600">Triage</a>
            </div>
            <div class="card-body" style="padding-top:8px">
                @forelse ($openReports as $item)
                    <div class="info-row">
                        <i class="fas fa-flag"></i>
                        <div style="min-width:0">
                            <div class="ir-value">
                                <a href="{{ route('backend.reports.show', $item) }}">{{ $item->reportedUser?->name }}</a>
                            </div>
                            <div class="ir-label">{{ ucwords(str_replace('_', ' ', $item->reason)) }} · {{ $item->created_at?->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state" style="padding:24px 8px">
                        <i class="fa-regular fa-circle-check" style="color:var(--success)"></i>
                        <h3>All clear</h3>
                        <p>No reports waiting for attention.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection