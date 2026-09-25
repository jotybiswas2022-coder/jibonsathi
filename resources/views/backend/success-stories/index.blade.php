@extends('backend.layouts.app')

@section('title', 'Success Stories')
@section('crumb', 'Content · Couple stories on the site')

@section('content')
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:18px">
        <div class="tabs" style="margin:0;border:none">
            @foreach (['all' => 'All', 'published' => 'Published', 'draft' => 'Drafts'] as $key => $label)
                <a class="tab {{ ($status ?? 'all') === $key ? 'active' : '' }}" href="{{ route('backend.success-stories.index', ['status' => $key]) }}">
                    {{ $label }} <span class="tab-count">{{ $counts[$key] ?? 0 }}</span>
                </a>
            @endforeach
        </div>
        <a href="{{ route('backend.success-stories.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New Story</a>
    </div>

    <form method="GET" action="{{ route('backend.success-stories.index') }}" class="filter-bar">
        <input type="hidden" name="status" value="{{ $status ?? 'all' }}">
        <div class="field">
            <label>Search</label>
            <input type="search" name="q" class="input" value="{{ $filters['q'] ?? '' }}" placeholder="Title or couple names">
        </div>
        <div class="field actions">
            <button class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('backend.success-stories.index') }}" class="btn btn-outline">Reset</a>
        </div>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Story</th>
                    <th>Couple</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <th>Married</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stories as $story)
                    <tr>
                        <td style="max-width:260px">
                            <div class="cu-name"><a href="{{ route('backend.success-stories.edit', $story) }}">{{ $story->title }}</a></div>
                            <div class="cu-sub">Order {{ $story->sort_order }}</div>
                        </td>
                        <td><strong>{{ $story->groom_name }}</strong> &amp; <strong>{{ $story->bride_name }}</strong></td>
                        <td class="text-muted">{{ $story->location }}</td>
                        <td>
                            <form method="POST" action="{{ route('backend.success-stories.toggle-publish', $story) }}"
                                  data-confirm-title="{{ $story->is_published ? 'Unpublish this story?' : 'Publish this story?' }}"
                                  data-confirm="{{ $story->is_published ? 'It disappears from the public success-stories page.' : 'It goes live on the public success-stories page straight away.' }}"
                                  data-confirm-ok="{{ $story->is_published ? 'Unpublish' : 'Publish' }}"
                                  data-confirm-icon="question"
                                  data-confirm-color="{{ $story->is_published ? '#6B7280' : '#16A34A' }}"
                                  data-confirm-focus-cancel>
                                @csrf
                                <button class="btn btn-{{ $story->is_published ? 'success-soft btn-sm' : 'warning-soft btn-sm' }}">
                                    {{ $story->is_published ? 'Published' : 'Draft' }}
                                </button>
                            </form>
                        </td>
                        <td>@if ($story->is_featured)<span class="badge badge-accent"><i class="fas fa-crown"></i> Featured</span>@else<span class="badge badge-muted">No</span>@endif</td>
                        <td class="text-muted">{{ $story->married_on?->format('M Y') ?? '—' }}</td>
                        <td style="text-align:right">
                            <a href="{{ route('success-stories.show', $story) }}" target="_blank" class="btn btn-ghost btn-sm"><i class="fas fa-external-link"></i></a>
                            <a href="{{ route('backend.success-stories.edit', $story) }}" class="btn btn-outline btn-sm"><i class="fas fa-pen"></i> Edit</a>
                            <form method="POST" action="{{ route('backend.success-stories.destroy', $story) }}"
                                  data-confirm-title="Delete this success story?"
                                  data-confirm="{{ $story->groom_name }} &amp; {{ $story->bride_name }} will be removed permanently."
                                  data-confirm-ok="Delete story" data-confirm-icon="error"
                                  data-confirm-color="#DC2626" style="display:inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger-soft btn-sm"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-heart"></i>
                                <h3>No stories yet</h3>
                                <p>Create your first success story to share a real couple's journey.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $stories->links('backend.components.pagination') }}
@endsection