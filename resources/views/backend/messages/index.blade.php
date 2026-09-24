@extends('backend.layouts.app')

@section('title', 'Conversations')
@section('crumb', 'Oversight only · admins never join member chats')

@section('content')
    <form method="GET" action="{{ route('backend.messages.index') }}" class="filter-bar">
        <div class="field">
            <label>Search</label>
            <input type="search" name="q" class="input" value="{{ $filters['q'] ?? '' }}" placeholder="Member name">
        </div>
        <div class="field">
            <label>Only flagged</label>
            <select name="only_flagged" class="input">
                <option value="">All conversations</option>
                <option value="1" @selected(! empty($filters['only_flagged']))>Flagged members only</option>
            </select>
        </div>
        <div class="field actions">
            <button class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('backend.messages.index') }}" class="btn btn-outline">Reset</a>
        </div>
    </form>

    <p class="text-muted text-small mb-3" style="margin-bottom:12px"><i class="fas fa-info-circle"></i> {{ number_format($total) }} total conversations. Latest activity shown first.</p>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Members</th>
                    <th>Last Message</th>
                    <th>Last Activity</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($conversations as $c)
                    <tr>
                        <td>
                            <div class="cell-user">
                                <div style="display:flex">
                                    <span class="avatar avatar-sm" style="margin-right:-8px;border:2px solid #fff"><img src="{{ $c->userOne?->primaryPhoto?->url() ?? \App\Support\Media::avatar($c->userOne?->name ?? 'Jora') }}" alt="" onerror="this.style.display='none'"><span class="initials">{{ $c->userOne?->initials ?? 'J' }}</span></span>
                                    <span class="avatar avatar-sm" style="border:2px solid #fff"><img src="{{ $c->userTwo?->primaryPhoto?->url() ?? \App\Support\Media::avatar($c->userTwo?->name ?? 'Jora') }}" alt="" onerror="this.style.display='none'"><span class="initials">{{ $c->userTwo?->initials ?? 'J' }}</span></span>
                                </div>
                                <div>
                                    <div class="cu-name">{{ $c->userOne?->name ?? 'Deleted' }} &amp; {{ $c->userTwo?->name ?? 'Deleted' }}</div>
                                    <div class="cu-sub">{{ $c->userOne?->email }} · {{ $c->userTwo?->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-muted" style="max-width:320px">
                            @if ($c->latestMessage)
                                <span style="display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                    <strong>{{ $c->latestMessage->sender?->name }}:</strong> {{ $c->latestMessage->body }}
                                </span>
                            @else
                                <span class="text-muted">No messages</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $c->last_message_at?->diffForHumans() }}</td>
                        <td style="text-align:right">
                            <a href="{{ route('backend.messages.show', $c) }}" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i> View Thread</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <i class="fas fa-comments"></i>
                                <h3>No conversations</h3>
                                <p>Nothing matches this filter.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $conversations->links('backend.components.pagination') }}
@endsection