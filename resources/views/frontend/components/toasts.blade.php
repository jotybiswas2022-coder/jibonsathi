@if (session('success'))
    <div class="toast success" data-toast="4200">
        <i class="fas fa-circle-check"></i>
        <div><p>{{ session('success') }}</p></div>
    </div>
@endif

@if (session('warning'))
    <div class="toast warning" data-toast="5000">
        <i class="fas fa-triangle-exclamation"></i>
        <div><p>{{ session('warning') }}</p></div>
    </div>
@endif

@if (@app()->isLocal() && session('debug_code'))
    <div class="toast info toast-info" data-toast="9000" style="border-left-color:var(--info)">
        <i class="fas fa-mobile-screen"></i>
        <div><p><strong>Verification code:</strong> <code>{{ session('debug_code') }}</code></p></div>
    </div>
@endif

@if ($errors->any())
    @foreach ($errors->all() as $error)
        <div class="toast error" data-toast="6000">
            <i class="fas fa-circle-exclamation"></i>
            <div><p>{{ $error }}</p></div>
        </div>
    @endforeach
@endif