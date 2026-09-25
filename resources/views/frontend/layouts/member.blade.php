@extends('frontend.layouts.app')

@section('title', $title ?? 'My Account')

@section('navbar-start')
    {{-- Stays in the sticky navbar, so every section is one tap away on mobile. --}}
    <button type="button" class="btn-icon side-toggle" data-sidebar-toggle
            aria-label="Open section menu" aria-expanded="false" aria-controls="memberSidebar">
        <i class="fas fa-ellipsis-vertical"></i>
    </button>
@endsection

@section('content')
    @include('frontend.components.member-shell')
@endsection
