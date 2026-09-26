@extends('backend.layouts.app')

@section('title', 'Edit Story')
@section('crumb', 'Content · Success stories · Edit')

@section('content')
    <div class="page-head">
        <div class="page-head-text">
            <h2>Edit Story</h2>
            <p>{{ $story->coupleLabel() }}@if ($story->married_on) · married {{ $story->married_on->format('M Y') }}@endif</p>
        </div>
        <div class="page-head-actions">
            <a href="{{ route('success-stories.show', $story) }}" target="_blank" rel="noopener" class="btn btn-outline">
                <i class="fas fa-external-link"></i> View live
            </a>
            <a href="{{ route('backend.success-stories.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to stories
            </a>
        </div>
    </div>

    @include('backend.success-stories._form')
@endsection
