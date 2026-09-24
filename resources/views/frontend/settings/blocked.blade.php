@extends('frontend.layouts.member')

@section('title', 'Blocked Users')

@section('member-content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Settings</h1>
            <p class="page-sub">Members you have blocked.</p>
        </div>
    </div>

    @include('frontend.settings.partials.nav', ['active' => 'blocked'])

    @if (empty($blocked))
        <x-frontend::empty-state :icon="'fa-ban'" :title="'No blocked users'"
            :description="'Blocked members won\u2019t be able to interact with you.'" />
    @else
        <div class="card">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Blocked On</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($blocked as $item)
                            @php($user = $item->blocked)
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <x-frontend::avatar :user="$user" :size="36" />
                                        <strong style="color:var(--text)">{{ $user->name }}</strong>
                                    </div>
                                </td>
                                <td class="text-muted">{{ $item->created_at?->diffForHumans() }}</td>
                                <td style="text-align:right">
                                    <form method="POST" action="{{ route('blocks.destroy', $user) }}">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-soft btn-sm"><i class="fas fa-unlock"></i> Unblock</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection