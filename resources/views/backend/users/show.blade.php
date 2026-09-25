@extends('backend.layouts.app')

@php
    use App\Support\Media;
    $badge = ['active' => 'badge-success', 'pending' => 'badge-warning', 'suspended' => 'badge-danger', 'inactive' => 'badge-muted'];
    $isVerified = ($user->profile?->verification_status ?? '') === 'verified';
@endphp

@section('title', $user->name)
@section('crumb', 'Members · Member details')

@section('content')
    @if (session('success'))
        <div class="alert alert-success" data-auto-close><i class="fas fa-circle-check"></i> {{ session('success') }}</div>
    @endif

    <div class="grid" style="grid-template-columns: 320px 1fr">
        <div class="card card-pad" style="text-align:center">
            <span class="avatar" style="width:96px;height:96px;margin:0 auto 14px;border-radius:24px">
                <img src="{{ $user->primaryPhoto?->url() ?? Media::avatar($user->name) }}" alt="" onerror="this.style.display='none'">
                <span class="initials" style="font-size:30px">{{ $user->initials }}</span>
            </span>
            <h3 style="margin-bottom:2px">{{ $user->name }}</h3>
            <p class="text-muted" style="font-size:13px">{{ $user->email }} · {{ $user->phone }}</p>
            <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin:10px 0 4px">
                <span class="badge {{ $badge[$user->status] ?? 'badge-muted' }}">{{ ucfirst($user->status) }}</span>
                @if ($isVerified)
                    <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Verified</span>
                @else
                    <span class="badge badge-muted">Not verified</span>
                @endif
                @if ($user->is_admin)
                    <span class="badge badge-accent"><i class="fas fa-crown"></i> Admin</span>
                @else
                    <span class="badge badge-info">Member</span>
                @endif
            </div>
            <p class="text-tiny text-muted mb-4">Joined {{ $user->created_at?->format('d M Y') }} · Last seen {{ $user->lastSeenLabel() }}</p>

            <div style="display:flex;flex-direction:column;gap:8px;text-align:left">
                <a href="{{ route('backend.users.edit', $user) }}" class="btn btn-outline btn-sm"><i class="fas fa-pen"></i> Edit Member</a>
                @unless ($isVerified)
                    <form method="POST" action="{{ route('backend.users.verify', $user) }}"
                          data-confirm-title="Approve and verify?"
                          data-confirm="{{ $user->name }} will get a verified badge and full access to messaging."
                          data-confirm-ok="Approve &amp; verify" data-confirm-icon="success"
                          data-confirm-color="#16A34A" data-confirm-focus-cancel>
                        @csrf
                        <button class="btn btn-success-soft btn-sm btn-block"><i class="fas fa-check"></i> Approve & Verify Profile</button>
                    </form>
                @endunless
                @if ($user->status !== 'suspended' && ! $user->is_admin)
                    <form method="POST" action="{{ route('backend.users.suspend', $user) }}"
                          data-confirm-title="Suspend {{ $user->name }}?"
                          data-confirm="They are signed out, blocked from the site, and disappear from search results."
                          data-confirm-ok="Suspend member" data-confirm-icon="error"
                          data-confirm-color="#DC2626">
                        @csrf
                        <button class="btn btn-danger-soft btn-sm btn-block"><i class="fas fa-ban"></i> Suspend</button>
                    </form>
                @elseif ($user->status === 'suspended')
                    <form method="POST" action="{{ route('backend.users.activate', $user) }}"
                          data-confirm-title="Reactivate {{ $user->name }}?"
                          data-confirm="The member signs in again and appears in search results straight away."
                          data-confirm-ok="Reactivate" data-confirm-icon="question"
                          data-confirm-color="#16A34A" data-confirm-focus-cancel>
                        @csrf
                        <button class="btn btn-success-soft btn-sm btn-block"><i class="fas fa-rotate-left"></i> Reactivate</button>
                    </form>
                @endif
                @if (! $user->is_admin)
                    <form method="POST" action="{{ route('backend.users.destroy', $user) }}"
                          data-confirm-title="Delete {{ $user->name }} permanently?"
                          data-confirm="The member, their messages, photos and reports are removed. This cannot be undone."
                          data-confirm-ok="Yes, delete everything" data-confirm-icon="error"
                          data-confirm-color="#DC2626">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-sm btn-block" style="color:var(--danger)"><i class="fas fa-trash"></i> Delete Member</button>
                    </form>
                @endif
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:20px;min-width:0">
            <div class="grid" style="grid-template-columns:repeat(4,1fr)">
                @foreach ([
                    ['n' => number_format($activity['interests_sent']), 'l' => 'Interests sent', 'i' => 'fa-heart', 'c' => 'ic-brand'],
                    ['n' => number_format($activity['interests_received']), 'l' => 'Interests received', 'i' => 'fa-envelope-open-text', 'c' => 'ic-accent'],
                    ['n' => number_format($activity['profile_views']), 'l' => 'Profile views', 'i' => 'fa-eye', 'c' => 'ic-info'],
                    ['n' => number_format($activity['reports']), 'l' => 'Reports received', 'i' => 'fa-flag', 'c' => 'ic-danger'],
                ] as $a)
                    <div class="card stat-tile">
                        <span class="st-ico {{ $a['c'] }}"><i class="fas {{ $a['i'] }}"></i></span>
                        <span class="st-num">{{ $a['n'] }}</span>
                        <span class="st-lbl">{{ $a['l'] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="card">
                <div class="card-head"><h3>Profile Details</h3></div>
                <div class="card-body">
                    <div class="grid" style="grid-template-columns:1fr 1fr 1fr">
                        <div>
                            <div class="section-block-title"><i class="fas fa-user"></i> Basic</div>
                            @if ($user->profile)
                                @foreach ([
                                    ['Gender', $user->genderLabel()],
                                    ['Marital status', $user->profile->marital_status ? ucfirst(str_replace('_',' ',$user->profile->marital_status)) : '—'],
                                    ['Birth date', $user->profile->date_of_birth?->format('d M Y')],
                                    ['Height', $user->profile->height_cm ? $user->profile->heightLabel().' ('.$user->profile->height_cm.' cm)' : '—'],
                                    ['Religion', $user->profile->religion ? ucfirst($user->profile->religion) : '—'],
                                    ['Country', $user->profile->country],
                                ] as [$k, $v])
                                    <div class="info-row"><i class="fas fa-grip-lines"></i><div style="min-width:0"><div class="ir-label">{{ $k }}</div><div class="ir-value">{{ $v ?? '—' }}</div></div></div>
                                @endforeach
                            @else
                                <p class="text-muted">No profile record yet.</p>
                            @endif
                        </div>
                        <div>
                            <div class="section-block-title"><i class="fas fa-graduation-cap"></i> Education & Career</div>
                            <div class="info-row"><i class="fas fa-grip-lines"></i><div style="min-width:0"><div class="ir-label">Education</div><div class="ir-value">{{ $user->education?->level ?? '—' }}</div></div></div>
                            <div class="info-row"><i class="fas fa-grip-lines"></i><div style="min-width:0"><div class="ir-label">Field</div><div class="ir-value">{{ $user->education?->field ?? '—' }}</div></div></div>
                            <div class="info-row"><i class="fas fa-grip-lines"></i><div style="min-width:0"><div class="ir-label">Occupation</div><div class="ir-value">{{ $user->occupation?->occupation ?? '—' }}</div></div></div>
                            <div class="info-row"><i class="fas fa-grip-lines"></i><div style="min-width:0"><div class="ir-label">Company</div><div class="ir-value">{{ $user->occupation?->company ?? '—' }}</div></div></div>
                        </div>
                        <div>
                            <div class="section-block-title"><i class="fas fa-map-location-dot"></i> Location</div>
                            <div class="info-row"><i class="fas fa-grip-lines"></i><div style="min-width:0"><div class="ir-label">City / District</div><div class="ir-value">{{ $user->profile?->locationLabel() ?? '—' }}</div></div></div>
                            <div class="info-row"><i class="fas fa-grip-lines"></i><div style="min-width:0"><div class="ir-label">Division</div><div class="ir-value">{{ $user->profile?->division ?? '—' }}</div></div></div>
                            <div class="info-row"><i class="fas fa-grip-lines"></i><div style="min-width:0"><div class="ir-label">Family type</div><div class="ir-value">{{ $user->familyDetail?->family_type ? ucfirst(str_replace('_',' ',$user->familyDetail->family_type)) : '—' }}</div></div></div>
                            <div class="info-row"><i class="fas fa-grip-lines"></i><div style="min-width:0"><div class="ir-label">Diet</div><div class="ir-value">{{ ucfirst($user->lifestyleDetail?->diet ?? '—') }}</div></div></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid" style="grid-template-columns:1fr 1fr">
                <div class="card">
                    <div class="card-head"><h3>Photos ({{ $user->photos->count() }})</h3></div>
                    <div class="card-body">
                        @if ($user->photos->isEmpty())
                            <p class="text-muted">No photos uploaded.</p>
                        @else
                            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(70px,1fr));gap:10px">
                                @foreach ($user->photos as $photo)
                                    <div style="position:relative;border-radius:12px;overflow:hidden;border:2px solid {{ $photo->is_primary ? 'var(--accent)' : 'transparent' }}">
                                        <img src="{{ $photo->url() }}" alt="" style="width:100%;aspect-ratio:1;object-fit:cover">
                                        @if ($photo->is_primary)
                                            <span style="position:absolute;top:4px;right:4px;background:var(--accent);color:var(--brand-800);font-size:10px;font-weight:800;padding:1px 6px;border-radius:999px">PRIMARY</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-head"><h3>Identity Verifications</h3></div>
                    <div class="card-body">
                        @forelse ($user->verifications as $v)
                            <div class="info-row">
                                <i class="fas fa-shield-halved"></i>
                                <div style="min-width:0;flex:1">
                                    <div class="ir-value">{{ ucfirst(str_replace('_',' ',$v->type)) }} — <span class="badge badge-{{ $v->status === 'approved' ? 'success' : ($v->status === 'rejected' ? 'danger' : 'warning') }}" style="font-size:10.5px">{{ ucfirst($v->status) }}</span></div>
                                    <div class="ir-label">Submitted {{ $v->created_at?->diffForHumans() }} · {{ $v->admin_note ? 'Note: '.$v->admin_note : 'No note' }}</div>
                                </div>
                                @if ($v->document_path)
                                    <a href="{{ route('backend.verification.document', $v) }}" target="_blank" class="btn btn-soft btn-sm"><i class="fas fa-file"></i> Doc</a>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted">No identity verification requests.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="grid" style="grid-template-columns:1fr 1fr">
                <div class="card">
                    <div class="card-head"><h3>Reports Received</h3></div>
                    <div class="card-body">
                        @forelse ($user->reportsReceived as $r)
                            <div class="info-row">
                                <i class="fas fa-flag"></i>
                                <div style="min-width:0;flex:1">
                                    <div class="ir-value">{{ ucwords(str_replace('_',' ',$r->reason)) }}</div>
                                    <div class="ir-label">By {{ $r->reporter?->name }} · {{ $r->created_at?->diffForHumans() }}</div>
                                </div>
                                <a href="{{ route('backend.reports.show', $r) }}" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i></a>
                            </div>
                        @empty
                            <p class="text-muted">No reports received.</p>
                        @endforelse
                    </div>
                </div>

                <div class="card">
                    <div class="card-head"><h3>Reports Made</h3></div>
                    <div class="card-body">
                        @forelse ($user->reportsMade as $r)
                            <div class="info-row">
                                <i class="fas fa-flag"></i>
                                <div style="min-width:0;flex:1">
                                    <div class="ir-value">{{ ucwords(str_replace('_',' ',$r->reason)) }}</div>
                                    <div class="ir-label">Against {{ $r->reportedUser?->name }} · {{ $r->created_at?->diffForHumans() }}</div>
                                </div>
                                <a href="{{ route('backend.reports.show', $r) }}" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i></a>
                            </div>
                        @empty
                            <p class="text-muted">No reports made.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection