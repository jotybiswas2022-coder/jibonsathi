@extends('backend.layouts.app')

@php
    use App\Support\Reference;
    use App\Support\Media;
@endphp

@section('title', 'Verification — '.($user->name ?? 'User'))
@section('crumb', 'Identity verification · Review')

@section('content')
    @if (session('success'))
        <div class="alert alert-success" data-auto-close><i class="fas fa-circle-check"></i> {{ session('success') }}</div>
    @endif

    <div class="grid" style="grid-template-columns: 1fr 340px">
        <div class="card card-pad">
            <div class="flex justify-between items-center" style="flex-wrap:wrap;gap:10px">
                <h3 class="card-title" style="margin:0"><i class="fas fa-file-shield"></i> Identity Document — {{ ucfirst(str_replace('_', ' ', $verification->type)) }}</h3>
                <span class="badge badge-{{ $verification->status === 'approved' ? 'success' : ($verification->status === 'rejected' ? 'danger' : 'warning') }}" style="font-size:13px">{{ ucfirst($verification->status) }}</span>
            </div>

            @if ($verification->document_path)
                <div style="margin:18px 0;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden">
                    <iframe src="{{ route('backend.verification.document', $verification) }}#toolbar=0&view=FitH"
                            style="width:100%;height:520px;border:0" title="Identity document"></iframe>
                </div>
                <a href="{{ route('backend.verification.document', $verification) }}" target="_blank" class="btn btn-outline">
                    <i class="fas fa-download"></i> Open document in new tab
                </a>
            @else
                <div class="empty-state" style="padding:40px">
                    <i class="fas fa-file-circle-xmark"></i>
                    <h3>No document attached</h3>
                    <p>This {{ $verification->type }} verification was submitted without a file.</p>
                </div>
            @endif

            @if ($verification->admin_note)
                <div class="alert alert-info mt-4" style="margin-top:18px"><i class="fas fa-note-sticky"></i> Admin note: {{ $verification->admin_note }}</div>
            @endif
        </div>

        <div style="display:flex;flex-direction:column;gap:20px">
            <div class="card card-pad" style="text-align:center">
                <span class="avatar" style="width:72px;height:72px;margin:0 auto 12px;border-radius:20px">
                    <img src="{{ $user->primaryPhoto?->url() ?? Media::avatar($user->name) }}" alt="" onerror="this.style.display='none'">
                    <span class="initials" style="font-size:24px">{{ $user->initials }}</span>
                </span>
                <h3 style="margin-bottom:2px">{{ $user->name }}</h3>
                <p class="text-muted text-small">{{ $user->email }} · {{ $user->phone }}</p>
                <p class="text-tiny text-muted">Submitted {{ $verification->created_at?->diffForHumans() }} · Reviewed {{ $verification->reviewed_at?->diffForHumans() ?? 'not yet' }}</p>
                <a href="{{ route('backend.users.show', $user) }}" class="btn btn-soft btn-sm btn-block"><i class="fas fa-user"></i> View Member Profile</a>
            </div>

            <div class="card card-pad">
                <h3 class="card-title"><i class="fas fa-gavel"></i> Decision</h3>
                <form method="POST" action="{{ route('backend.verification.decide', $verification) }}">
                    @csrf
                    <div class="field">
                        <label for="note">Note (sent to member)</label>
                        <textarea name="note" id="note" class="input" rows="3" maxlength="500"
                                  placeholder="Optional note for the member">{{ old('note') }}</textarea>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:8px">
                        <button name="decision" value="approved" class="btn btn-success-soft btn-block" style="color:var(--success)"><i class="fas fa-check"></i> Approve Verification</button>
                        <button name="decision" value="rejected" class="btn btn-danger-soft btn-block" data-confirm-submit data-confirm="Reject this identity verification?"><i class="fas fa-xmark"></i> Reject</button>
                        <button name="decision" value="pending" class="btn btn-outline btn-block">Keep as Pending</button>
                    </div>
                </form>
            </div>

            <form method="POST" action="{{ route('backend.verification.destroy', $verification) }}"
                  data-confirm="Delete this verification record and its stored document?" style="text-align:center">
                @csrf @method('DELETE')
                <button class="btn btn-ghost btn-sm" style="color:var(--danger)"><i class="fas fa-trash"></i> Delete Record</button>
            </form>
        </div>
    </div>
@endsection