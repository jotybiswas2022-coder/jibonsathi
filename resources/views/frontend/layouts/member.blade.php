@extends('frontend.layouts.app')

@section('title', $title ?? 'My Account')

@section('content')
    @include('frontend.components.member-shell')
@endsection