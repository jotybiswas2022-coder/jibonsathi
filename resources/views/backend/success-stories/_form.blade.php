@php
    $isEditing = isset($story) && $story->exists;
@endphp

<form method="POST"
      action="{{ $isEditing ? route('backend.success-stories.update', $story) : route('backend.success-stories.store') }}"
      enctype="multipart/form-data">
    @csrf
    @if ($isEditing) @method('PUT') @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <i class="fas fa-circle-exclamation"></i>
            <div>
                Please fix the errors below:
                <ul style="margin:6px 0 0;padding-left:18px">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
            </div>
        </div>
    @endif

    <div class="field">
        <label for="title">Story Title</label>
        <input type="text" name="title" id="title" class="input" value="{{ old('title', $story->title) }}" maxlength="150" required>
    </div>

    <div class="field-group">
        <div class="field">
            <label for="groom_name">Groom's Name</label>
            <input type="text" name="groom_name" id="groom_name" class="input" value="{{ old('groom_name', $story->groom_name) }}" maxlength="80" required>
        </div>
        <div class="field">
            <label for="bride_name">Bride's Name</label>
            <input type="text" name="bride_name" id="bride_name" class="input" value="{{ old('bride_name', $story->bride_name) }}" maxlength="80" required>
        </div>
    </div>

    <div class="field-group">
        <div class="field">
            <label for="location">Location</label>
            <input type="text" name="location" id="location" class="input" value="{{ old('location', $story->location) }}" maxlength="120" placeholder="e.g. Dhaka, Bangladesh">
        </div>
        <div class="field">
            <label for="married_on">Marriage Date</label>
            <input type="date" name="married_on" id="married_on" class="input" value="{{ old('married_on', $story->married_on?->format('Y-m-d') ?? '') }}">
        </div>
    </div>

    <div class="field">
        <label for="story">Story</label>
        <textarea name="story" id="story" class="input" rows="8" maxlength="5000" data-count="5000" required>{{ old('story', $story->story) }}</textarea>
        <span class="text-muted text-tiny" style="align-self:flex-end" data-count-target></span>
    </div>

    <div class="field">
        <label for="photo">Couple Photo (optional)</label>
        <input type="file" name="photo" id="photo" class="input" accept="image/jpeg,image/png,image/webp">
        @if ($isEditing && $story->photo_path)
            <span class="text-tiny text-muted">Current photo: {{ $story->photo_path }} (uploading replaces it)</span>
        @endif
    </div>

    <div class="field-group">
        <div class="field">
            <label for="sort_order">Sort Order</label>
            <input type="number" name="sort_order" id="sort_order" class="input" value="{{ old('sort_order', $story->sort_order ?? 0) }}" min="0" max="999">
        </div>
        <div class="field">
            <label class="switch" style="margin-top:26px">
                <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $story->is_published ?? false))>
                <span class="track"></span>
                <span>Published on website</span>
            </label>
        </div>
        <div class="field">
            <label class="switch" style="margin-top:26px">
                <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $story->is_featured ?? false))>
                <span class="track"></span>
                <span>Featured (crown badge)</span>
            </label>
        </div>
    </div>

    <div class="flex gap-2" style="justify-content:space-between;margin-top:6px">
        <a href="{{ route('backend.success-stories.index') }}" class="btn btn-outline">Cancel</a>
        <button class="btn btn-primary"><i class="fas fa-save"></i> {{ $isEditing ? 'Save Changes' : 'Create Story' }}</button>
    </div>
</form>