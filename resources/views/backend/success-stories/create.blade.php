@extends('backend.layouts.app')

@php
    $isEditing = isset($story) && $story->exists;
@endphp

@section('title', $isEditing ? 'Edit Story' : 'New Success Story')
@section('crumb', 'Success stories · '.($isEditing ? 'Edit' : 'Create'))

@section('content')
    <div class="card card-pad" style="max-width:820px">
        @include('backend.success-stories._form')
    </div>
@endsection