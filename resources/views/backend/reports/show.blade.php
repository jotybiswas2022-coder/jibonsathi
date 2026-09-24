@extends('backend.layouts.app')

@php
    use App\Support\Media;
@endphp

@section('title', 'Report — '.($report->reportedUser?->name ?? 'User'))
@section('crumb', 'Reports · Case review')

@section('content')
    @if (session('success'))
        <div class="alert alert-success" data-auto-close><i class="fas fa-circle-check"></i> {{ session('success') }}</div>
    @endif

    <div class="grid" style="grid-template-columns: 1fr 320px">
        <div style="display:flex;flex-direction:column;gap:20px;min-width:0">
            <div class="card card-pad">
                <div class="flex justify-between items-center" style="flex-wrap:wrap;gap:10px">
                    <h3 class="card-title" style="margin:0"><i class="fas fa-flag"></i> {{ \App\Models\Report::REASONS[$report->reason] ?? $report->reason }}</h3>
                    <span class="badge badge-{{ $report->status === 'pending' ? 'warning' : ($report->status === 'resolved' ? 'success' : ($report->status === 'dismissed' ? 'muted' : 'info')) }}" style="font-size:13px">{{ ucfirst($report->status) }}</span>
                </div>
                <div class="alert alert-warning mt-4" style="margin-top:16px">
                    <i class="fas fa-quote-left"></i>
                    <div>
                        <strong>Reporter's description:</strong>
                        <div style="white-space:pre-wrap;margin-top:4px">{{ $report->description }}</div>
                    </div>
                </div>
                <p class="text-tiny text-muted" style="margin:12px 0 0">
                    Reported {{ $report->created_at?->diffForHumans() }}
                    @if ($report->handler)<span> · Handled by {{ $report->handler?->name }}</span>@endif
                    @if ($report->block_user)<span> · Reporter asked to block the member</span>@endif
                </p>
            </div>

            @if ($report->reportedUser?->photos?->isNotEmpty())
                <div class="card card-pad">
                    <h3 class="card-title"><i class="fas fa-images"></i> Reported Member's Photos</h3>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:10px">
                        @foreach ($report->reportedUser->photos as $photo)
                            <img src="{{ $photo->url() }}" alt="" style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:12px">
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($history->isNotEmpty())
                <div class="card card-pad">
                    <h3 class="card-title"><i class="fas fa-clock-rotate-left"></i> Previous Reports on This Member</h3>
                    @foreach ($history as $h)
                        <div class="info-row">
                            <i class="fas fa-flag"></i>
                            <div style="min-width:0;flex:1">
                                <div class="ir-value">{{ \App\Models\Report::REASONS[$h->reason] ?? $h->reason }} <span class="badge badge-muted" style="font-size:10.5px">{{ ucfirst($h->status) }}</span></div>
                                <div class="ir-label">By {{ $h->reporter?->name }} · {{ $h->created_at?->diffForHumans() }}</div>
                            </div>
                            <a href="{{ route('backend.reports.show', $h) }}" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i></a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div style="display:flex;flex-direction:column;gap:20px">
            <div class="card card-pad" style="text-align:center">
                <span class="avatar" style="width:76px;height:76px;margin:0 auto 12px;border-radius:20px">
                    <img src="{{ $report->reportedUser?->primaryPhoto?->url() ?? Media::avatar($report->reportedUser?->name ?? 'Jibon Sathi') }}" alt="" onerror="this.style.display='none'">
                    <span class="initials" style="font-size:26px">{{ $report->reportedUser?->initials ?? 'J' }}</span>
                </span>
                <h3 style="margin-bottom:2px">{{ $report->reportedUser?->name ?? 'Deleted user' }}</h3>
                <p class="text-muted text-small">{{ $report->reportedUser?->email ?? 'No longer on Jibon Sathi' }}</p>
                @if ($report->reportedUser)
                    <a href="{{ route('backend.users.show', $report->reportedUser) }}" class="btn btn-soft btn-sm btn-block"><i class="fas fa-user"></i> View Member</a>
                @endif
            </div>

            <div class="card card-pad">
                <h3 class="card-title"><i class="fas fa-gavel"></i> Resolve Case</h3>
                <form method="POST" action="{{ route('backend.reports.decide', $report) }}">
                    @csrf
                    <div class="field">
                        <label for="note">Admin note</label>
                        <textarea name="note" id="note" class="input" rows="3" maxlength="500"
                                  placeholder="Internal note on the decision…">{{ old('note') }}</textarea>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:8px">
                        <button name="decision" value="investigating" class="btn btn-warning" style="background:var(--warning-bg);color:var(--warning)"><i class="fas fa-magnifying-glass"></i> Mark Investigating</button>
                        <button name="decision" value="resolved" class="btn btn-success-soft" style="color:var(--success)"><i class="fas fa-check"></i> Resolve</button>
                        <button name="decision" value="dismissed" class="btn btn-outline"><i class="fas fa-xmark"></i> Dismiss</button>
                        <button name="decision" value="suspend" class="btn btn-danger" style="background:var(--danger);color:#fff" data-confirm-submit data-confirm="Suspend the reported member's account?"><i class="fas fa-ban"></i> Suspend Reported Member</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection