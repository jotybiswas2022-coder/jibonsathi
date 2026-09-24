@extends('frontend.layouts.app')

@section('title', 'Step 5 — Lifestyle')

@section('content')
<div class="container" style="max-width:760px;padding-block:40px">
    <div class="card card-pad" style="padding:clamp(22px,4vw,40px)">
        <div class="flex items-center justify-between mb-4">
            <a href="{{ route('home') }}" class="brand">
                <span class="brand-mark"><i class="fas fa-heart"></i></span>
                Jibon Sathi
            </a>
            <form method="POST" action="{{ route('register.skip') }}">@csrf<button class="btn btn-ghost btn-sm text-muted">Skip for now</button></form>
        </div>

        <x-frontend::wizard-progress :step="$step" :total="$total" />

        <h1 style="font-size:24px;margin-bottom:4px">Your lifestyle</h1>
        <p class="text-muted text-small mb-5" style="margin-bottom:22px">A little about your daily rhythm — compatibility is about the little things too.</p>

        <form method="POST" action="{{ route('register.step.store', ['step' => $step]) }}">
            @csrf
            @method('PUT')

            <div class="field-group">
                <div class="field">
                    <label for="diet">Diet</label>
                    <select name="diet" id="diet" class="input">
                        <option value="">Select</option>
                        @foreach (\App\Support\Reference::diets() as $key => $label)
                            <option value="{{ $key }}" @selected(old('diet', $profileUser->lifestyleDetail?->diet) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="smoking">Smoking</label>
                    <select name="smoking" id="smoking" class="input">
                        <option value="">Select</option>
                        @foreach (\App\Support\Reference::smoking() as $key => $label)
                            <option value="{{ $key }}" @selected(old('smoking', $profileUser->lifestyleDetail?->smoking) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="field">
                <label for="drinking">Alcohol</label>
                <select name="drinking" id="drinking" class="input">
                    <option value="">Select</option>
                    @foreach (\App\Support\Reference::drinking() as $key => $label)
                        <option value="{{ $key }}" @selected(old('drinking', $profileUser->lifestyleDetail?->drinking) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="section-title"><i class="fas fa-heart"></i> Hobbies <span class="text-muted text-tiny" style="font-weight:400">(choose any)</span></div>
            <div class="chip-list">
                @foreach (\App\Support\Reference::hobbies() as $hobby)
                    @php($checked = in_array($hobby, old('hobbies', $profileUser->lifestyleDetail?->hobbies ?? []), true))
                    <label class="chip @if($checked) checked @endif">
                        <input type="checkbox" name="hobbies[]" value="{{ $hobby }}" @if($checked) checked @endif>
                        {{ $hobby }}
                    </label>
                @endforeach
            </div>

            <div class="section-title mt-4"><i class="fas fa-star"></i> Interests <span class="text-muted text-tiny" style="font-weight:400">(choose any)</span></div>
            <div class="chip-list">
                @foreach (\App\Support\Reference::interests() as $interest)
                    @php($checked = in_array($interest, old('interests', $profileUser->lifestyleDetail?->interests ?? []), true))
                    <label class="chip @if($checked) checked @endif">
                        <input type="checkbox" name="interests[]" value="{{ $interest }}" @if($checked) checked @endif>
                        {{ $interest }}
                    </label>
                @endforeach
            </div>

            <div class="field mt-4">
                <label for="about_lifestyle">About My Lifestyle</label>
                <textarea name="about_lifestyle" id="about_lifestyle" class="input" maxlength="900" data-count="900"
                          placeholder="Anything else you'd like to share about your lifestyle, routines or values.">{{ old('about_lifestyle', $profileUser->lifestyleDetail?->about_lifestyle) }}</textarea>
                <span class="text-tiny text-muted" style="text-align:right" data-count-target></span>
            </div>

            <div class="flex justify-between mt-5" style="gap:12px">
                <a href="{{ route('register.step', ['step' => 4]) }}" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back</a>
                <button type="submit" class="btn btn-primary btn-lg">Save & Continue <i class="fas fa-arrow-right"></i></button>
            </div>
        </form>
    </div>
</div>
@endsection