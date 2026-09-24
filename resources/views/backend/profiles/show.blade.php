@extends('backend.layouts.app')

@php
    use App\Support\Reference;
    use App\Support\Media;
    $p = $profile;
    $u = $user;
    $isVerified = ($p->verification_status ?? '') === 'verified';
@endphp

@section('title', 'Review — '.($u->name ?? 'Profile'))
@section('crumb', 'Profiles · Moderation')

@section('content')
    @if (session('success'))
        <div class="alert alert-success" data-auto-close><i class="fas fa-circle-check"></i> {{ session('success') }}</div>
    @endif

    <div class="grid" style="grid-template-columns: 300px 1fr">
        <div class="card card-pad" style="text-align:center;position:sticky;top:90px;align-self:start">
            <span class="avatar" style="width:88px;height:88px;margin:0 auto 14px;border-radius:22px">
                <img src="{{ $u->primaryPhoto?->url() ?? Media::avatar($u->name) }}" alt="" onerror="this.style.display='none'">
                <span class="initials" style="font-size:28px">{{ $u->initials }}</span>
            </span>
            <h3 style="margin-bottom:2px">{{ $u->name }}</h3>
            <p class="text-muted" style="font-size:13px">{{ $u->email }} · {{ $u->phone }}</p>
            <p style="margin:8px 0">
                <span class="badge badge-{{ $p->profile_status === 'approved' ? 'success' : ($p->profile_status === 'rejected' ? 'danger' : 'warning') }}" style="font-size:12px">
                    {{ ucfirst($p->profile_status) }}
                </span>
                @if ($isVerified)
                    <span class="badge badge-success" style="font-size:12px"><i class="fa-solid fa-circle-check"></i> Identity verified</span>
                @endif
            </p>

            <div style="text-align:left;margin:14px 0">
                <div class="flex justify-between" style="font-size:12.5px;color:var(--muted)"><span>Profile completion</span><strong style="color:var(--text)">{{ $p->profile_completion }}%</strong></div>
                <div class="progress" style="margin-top:5px"><span style="width:{{ $p->profile_completion }}%"></span></div>
            </div>

            <div style="border-top:1px solid var(--border);padding-top:14px;text-align:left">
                @php
                    $photos = $u->photos->filter(fn ($ph) => $ph->isApproved());
                @endphp
                <div class="section-block-title" style="font-size:13px;margin-bottom:8px"><i class="fas fa-images"></i> Approved Photos ({{ $photos->count() }})</div>
                @if ($photos->isEmpty())
                    <p class="text-muted text-small">No approved photos.</p>
                @else
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px">
                        @foreach ($photos as $photo)
                            <div style="border-radius:10px;overflow:hidden;position:relative">
                                <img src="{{ $photo->url() }}" alt="" style="width:100%;aspect-ratio:1;object-fit:cover">
                                <form method="POST" action="{{ route('backend.profiles.photos.destroy', [$profile, $photo]) }}" data-confirm="Remove this photo from the gallery?" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.45);opacity:0;transition:.15s">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:20px;min-width:0">
            <div class="card card-pad">
                <h3 class="card-title"><i class="fas fa-id-card"></i> Member Information</h3>
                <div class="grid" style="grid-template-columns:repeat(3,1fr)">
                    <div class="info-row"><i class="fas fa-user"></i><div style="min-width:0"><div class="ir-label">Gender</div><div class="ir-value">{{ ucfirst($p->gender ?? '—') }}</div></div></div>
                    <div class="info-row"><i class="fas fa-cake-candles"></i><div style="min-width:0"><div class="ir-label">Age</div><div class="ir-value">{{ $p->ageGroup() }}</div></div></div>
                    <div class="info-row"><i class="fas fa-ruler-vertical"></i><div style="min-width:0"><div class="ir-label">Height</div><div class="ir-value">{{ $p->heightLabel() }}</div></div></div>
                    <div class="info-row"><i class="fas fa-star"></i><div style="min-width:0"><div class="ir-label">Marital status</div><div class="ir-value">{{ Reference::label($p->marital_status, 'marital_status') }}</div></div></div>
                    <div class="info-row"><i class="fas fa-place-of-worship"></i><div style="min-width:0"><div class="ir-label">Religion</div><div class="ir-value">{{ Reference::label($p->religion, 'religion') }}</div></div></div>
                    <div class="info-row"><i class="fas fa-map-location-dot"></i><div style="min-width:0"><div class="ir-label">Location</div><div class="ir-value">{{ $p->locationLabel() }}</div></div></div>
                    <div class="info-row"><i class="fas fa-graduation-cap"></i><div style="min-width:0"><div class="ir-label">Education</div><div class="ir-value">{{ $u->education?->level ?? '—' }}</div></div></div>
                    <div class="info-row"><i class="fas fa-briefcase"></i><div style="min-width:0"><div class="ir-label">Occupation</div><div class="ir-value">{{ $u->occupation?->designation ?: ($u->occupation?->occupation ?? '—') }}</div></div></div>
                    <div class="info-row"><i class="fas fa-users"></i><div style="min-width:0"><div class="ir-label">Family type</div><div class="ir-value">{{ Reference::label($u->familyDetail?->family_type, 'family_type') }}</div></div></div>
                </div>
            </div>

            @if ($u->verifications->isNotEmpty())
                <div class="card card-pad">
                    <h3 class="card-title"><i class="fas fa-shield-halved"></i> Identity Verification</h3>
                    @foreach ($u->verifications as $v)
                        <div class="info-row">
                            <i class="fas fa-shield-halved"></i>
                            <div style="min-width:0;flex:1">
                                <div class="ir-value">{{ ucfirst(str_replace('_',' ',$v->type)) }} <span class="badge badge-{{ $v->status === 'approved' ? 'success' : ($v->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($v->status) }}</span></div>
                                <div class="ir-label">{{ $v->admin_note ?? 'No admin note' }} · submitted {{ $v->created_at?->diffForHumans() }}</div>
                            </div>
                            @if ($v->document_path)
                                <a href="{{ route('backend.verification.document', $v) }}" target="_blank" class="btn btn-soft btn-sm"><i class="fas fa-file"></i> Document</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($u->lifestyleDetail?->about_lifestyle)
                <div class="card card-pad">
                    <h3 class="card-title"><i class="fas fa-quote-left"></i> About Themselves</h3>
                    <p class="text-muted" style="white-space:pre-wrap;margin:0">{{ $u->lifestyleDetail->about_lifestyle }}</p>
                </div>
            @endif
        </div>
    </div>

    <div class="card card-pad mt-4" style="max-width:640px">
        <h3 class="card-title"><i class="fas fa-gavel"></i> Moderation Decision</h3>
        <form method="POST" action="{{ route('backend.profiles.moderate', $profile) }}">
            @csrf
            <div class="field">
                <label for="note">Note to member (optional)</label>
                <textarea name="note" id="note" class="input" rows="3" maxlength="500"
                          placeholder="e.g. Please add a clear photo, or we removed the listed information because…">{{ old('note') }}</textarea>
            </div>
            <div class="flex gap-2" style="flex-wrap:wrap">
                <button name="decision" value="approved" class="btn btn-success-soft"><i class="fas fa-check"></i> Approve Profile</button>
                <button name="decision" value="pending" class="btn btn-warning" style="background:var(--warning-bg);color:var(--warning)"><i class="fas fa-clock"></i> Back to Pending</button>
                <button name="decision" value="rejected" class="btn btn-danger-soft" data-confirm-submit data-confirm="Reject this profile? The member will be notified."><i class="fas fa-xmark"></i> Reject</button>
                <button name="decision" value="suspended" class="btn btn-danger" data-confirm-submit data-confirm="Suspend this member's account?" style="background:var(--danger);color:#fff"><i class="fas fa-ban"></i> Suspend Account</button>
            </div>
        </form>
    </div>
@endsection