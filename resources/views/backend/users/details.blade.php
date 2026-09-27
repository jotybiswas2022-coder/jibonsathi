@extends('backend.layouts.app')

@section('title', $user->name.' — All Details')
@section('crumb', 'Members · '.$user->name.' · All details')

@section('content')
    <div class="page-head">
        <div class="page-head-text">
            <h2>All Details</h2>
            <p>Everything on {{ $user->name }}’s record in one place. What you save here replaces what is on file, including clearing a field the member filled in themselves.</p>
        </div>
        <div class="page-head-actions">
            <a href="{{ route('backend.users.show', $user) }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to profile</a>
            <a href="{{ route('backend.users.edit', $user) }}" class="btn btn-ghost"><i class="fas fa-pen"></i> Account only</a>
        </div>
    </div>

    @include('backend.users._form', [
        'user' => $user,
        'statuses' => $statuses,
        'action' => route('backend.users.details.update', $user),
    ])
@endsection
