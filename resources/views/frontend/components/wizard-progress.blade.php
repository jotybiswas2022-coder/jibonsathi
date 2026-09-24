@props(['step' => 2, 'total' => 6])

@php($labels = \App\Services\ProfileService::STEPS)

<div class="wizard-bar">
    @for ($i = 1; $i <= $total; $i++)
        <div class="wizard-dot {{ $i < $step ? 'done' : ($i == $step ? 'current' : '') }}" title="{{ $labels[$i] ?? '' }}"></div>
    @endfor
</div>

<div class="wizard-label">
    <span class="wl-step">{{ $step }}</span>
    <div>
        <div style="font-weight:700;font-size:15px">{{ $labels[$step] ?? '' }}</div>
        <div class="text-tiny text-muted">Step {{ $step }} of {{ $total }}</div>
    </div>
    <div style="margin-left:auto;text-align:right">
        <span class="text-tiny text-muted">Profile completion</span>
        <div style="font-weight:800;font-family:var(--font-display);color:var(--brand)">{{ auth()->user()->completion() }}%</div>
    </div>
</div>