@php
    use Illuminate\Support\Str;
    $admin = auth()->user();
    $path = request()->path();
    $pendingVerifications = \App\Models\Verification::query()->where('status', 'pending')->count();
    $openReports = \App\Models\Report::query()->whereIn('status', ['pending', 'investigating'])->count();
    $active = fn (array $parts) => collect($parts)->contains(fn ($p) => Str::startsWith($path, $p));
@endphp
<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-brand">
        <span class="mark"><i class="fas fa-heart"></i></span>
        <span>Jibon Sathi Admin</span>
    </div>

    <nav class="side-nav">
        <div class="snav-label">Overview</div>
        <a href="{{ route('backend.dashboard.index') }}" class="{{ $path === 'admin' ? 'active' : '' }}">
            <i class="fas fa-gauge-high"></i> Dashboard
        </a>

        <div class="snav-label">Members</div>
        <a href="{{ route('backend.users.index') }}" class="{{ $active(['admin/users']) ? 'active' : '' }}">
            <i class="fas fa-users"></i> Members
        </a>
        <a href="{{ route('backend.profiles.index') }}" class="{{ $active(['admin/profiles']) ? 'active' : '' }}">
            <i class="fas fa-id-card"></i> Profiles
        </a>
        <a href="{{ route('backend.verification.index') }}" class="{{ $active(['admin/verifications']) ? 'active' : '' }}">
            <i class="fas fa-shield-halved"></i> Verify Identity
            @if ($pendingVerifications > 0)
                <span class="sbadge">{{ $pendingVerifications }}</span>
            @endif
        </a>

        <div class="snav-label">Moderation</div>
        <a href="{{ route('backend.reports.index') }}" class="{{ $active(['admin/reports']) ? 'active' : '' }}">
            <i class="fas fa-flag"></i> Reports
            @if ($openReports > 0)
                <span class="sbadge">{{ $openReports }}</span>
            @endif
        </a>
        <a href="{{ route('backend.messages.index') }}" class="{{ $active(['admin/messages']) ? 'active' : '' }}">
            <i class="fas fa-comments"></i> Messages
        </a>

        <div class="snav-label">Content</div>
        <a href="{{ route('backend.success-stories.index') }}" class="{{ $active(['admin/success-stories']) ? 'active' : '' }}">
            <i class="fas fa-heart"></i> Success Stories
        </a>

        <div class="snav-label">System</div>
        <a href="{{ route('backend.settings.index') }}" class="{{ $path === 'admin/settings' ? 'active' : '' }}">
            <i class="fas fa-sliders"></i> Site Settings
        </a>
    </nav>

    <div class="side-foot">
        <span class="initials">{{ $admin->initials }}</span>
        <div style="min-width:0">
            <div class="sf-name">{{ $admin->name }}</div>
            <div class="sf-sub">Super Admin</div>
        </div>
    </div>
</aside>