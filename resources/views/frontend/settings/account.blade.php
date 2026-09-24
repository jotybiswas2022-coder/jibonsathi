@extends('frontend.layouts.member')

@section('title', 'Account Settings')

@section('member-content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Settings</h1>
            <p class="page-sub">Manage your account.</p>
        </div>
    </div>

    @include('frontend.settings.partials.nav', ['active' => 'account'])

    <div class="card card-pad" style="max-width:640px;border-left:4px solid var(--warning,#f59e0b)">
        <h3 class="card-title"><i class="fas fa-pause"></i> Deactivate Account</h3>
        <p class="text-muted text-small mb-4">
            Your profile will be hidden and you'll be signed out. You can sign back in any time to reactivate it.
        </p>
        <form method="POST" action="{{ route('settings.account.deactivate') }}"
              data-confirm="Deactivate your account? Your profile will be hidden until you sign back in.">
            @csrf
            <button class="btn btn-warning"><i class="fas fa-pause"></i> Deactivate</button>
        </form>
    </div>

    <div class="card card-pad" style="max-width:640px;margin-top:20px;border-left:4px solid var(--danger,#e11d48)">
        <h3 class="card-title"><i class="fas fa-trash"></i> Delete Account</h3>
        <p class="text-muted text-small mb-4">
            Permanently remove your account, photos, messages and other personal data from Jibon Sathi. This cannot be undone.
        </p>
        <form method="POST" action="{{ route('settings.account.destroy') }}"
              data-confirm="This will permanently delete your account and all data. Are you absolutely sure?">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger"><i class="fas fa-trash"></i> Delete My Account</button>
        </form>
    </div>
@endsection