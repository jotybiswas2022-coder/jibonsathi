@extends('backend.layouts.app')

@section('title', 'New Success Story')
@section('crumb', 'Content · Success stories · Create')

@section('content')
    <div class="page-head">
        <div class="page-head-text">
            <h2>New Success Story</h2>
            <p>Add a couple's journey to the library. You can keep it as a draft and publish it later.</p>
        </div>
        <div class="page-head-actions">
            <a href="{{ route('backend.success-stories.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to stories
            </a>
        </div>
    </div>

    @include('backend.success-stories._form')
@endsection
