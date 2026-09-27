@extends('backend.layouts.app')

@section('title', 'Create Member')
@section('crumb', 'Members · Create a member')

@section('content')
    <div class="page-head">
        <div class="page-head-text">
            <h2>Create Member</h2>
            <p>One form for the whole record: the account they sign in with, everything on their profile, and what they are looking for. The name, email, phone, gender, profile status and password are required — fill in the rest as it comes in.</p>
        </div>
        <div class="page-head-actions">
            <a href="{{ route('backend.users.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to members</a>
        </div>
    </div>

    @include('backend.users._form', [
        'user' => null,
        'statuses' => $statuses,
        'action' => route('backend.users.store'),
    ])
@endsection
