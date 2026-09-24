@extends('frontend.layouts.member')

@section('title', 'Lifestyle Settings')

@section('member-content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Settings</h1>
            <p class="page-sub">Lifestyle and interests.</p>
        </div>
    </div>

    @include('frontend.settings.partials.nav', ['active' => 'lifestyle'])

    <div class="card card-pad" style="max-width:860px">
        <form method="POST" action="{{ route('settings.lifestyle.update') }}">
            @csrf
            @method('PUT')

            <div class="field-group">
                <div class="field">
                    <label for="diet">Diet</label>
                    <select name="diet" id="diet" class="input">
                        <option value="">Select</option>
                        @foreach (\App\Support\Reference::diets() as $key => $label)
                            <option value="{{ $key }}" @selected(old('diet', $user->lifestyleDetail?->diet) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="smoking">Smoking</label>
                    <select name="smoking" id="smoking" class="input">
                        <option value="">Select</option>
                        @foreach (\App\Support\Reference::smoking() as $key => $label)
                            <option value="{{ $key }}" @selected(old('smoking', $user->lifestyleDetail?->smoking) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="field">
                <label for="drinking">Alcohol</label>
                <select name="drinking" id="drinking" class="input">
                    <option value="">Select</option>
                    @foreach (\App\Support\Reference::drinking() as $key => $label)
                        <option value="{{ $key }}" @selected(old('drinking', $user->lifestyleDetail?->drinking) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="section-title"><i class="fas fa-heart"></i> Hobbies</div>
            <div class="chip-list">
                @foreach (\App\Support\Reference::hobbies() as $hobby)
                    @php($checked = in_array($hobby, old('hobbies', $user->lifestyleDetail?->hobbies ?? []), true))
                    <label class="chip @if($checked) checked @endif">
                        <input type="checkbox" name="hobbies[]" value="{{ $hobby }}" @if($checked) checked @endif>
                        {{ $hobby }}
                    </label>
                @endforeach
            </div>

            <div class="section-title mt-4"><i class="fas fa-star"></i> Interests</div>
            <div class="chip-list">
                @foreach (\App\Support\Reference::interests() as $interest)
                    @php($checked = in_array($interest, old('interests', $user->lifestyleDetail?->interests ?? []), true))
                    <label class="chip @if($checked) checked @endif">
                        <input type="checkbox" name="interests[]" value="{{ $interest }}" @if($checked) checked @endif>
                        {{ $interest }}
                    </label>
                @endforeach
            </div>

            <div class="field mt-4">
                <label for="about_lifestyle">About My Lifestyle</label>
                <textarea name="about_lifestyle" id="about_lifestyle" class="input" maxlength="900" data-count="900"
                          rows="4">{{ old('about_lifestyle', $user->lifestyleDetail?->about_lifestyle) }}</textarea>
                <span class="text-tiny text-muted" style="text-align:right" data-count-target></span>
            </div>

            <button class="btn btn-primary">Save Changes</button>
        </form>
    </div>
@endsection