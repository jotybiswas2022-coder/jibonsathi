@php
    $isEditing = isset($story) && $story->exists;
    $photoUrl = $isEditing ? $story->photoUrl() : null;
@endphp

<form method="POST"
      action="{{ $isEditing ? route('backend.success-stories.update', $story) : route('backend.success-stories.store') }}"
      enctype="multipart/form-data"
      data-form-kit
      data-confirm-title="{{ $isEditing ? 'Save changes to this story?' : 'Create this success story?' }}"
      data-confirm="{{ $isEditing ? 'Your edits replace the published story straight away.' : 'The story is added to the admin list. Publish it separately when it is ready to go live.' }}"
      data-confirm-ok="{{ $isEditing ? 'Save changes' : 'Create story' }}" data-confirm-icon="question"
      data-confirm-color="#8B1E3F" data-confirm-focus-cancel>
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

    <div class="story-form">
        {{-- ------------------------------- the couple ------------------------------ --}}
        <div class="story-form-main">
            <section class="card set-card">
                <div class="set-card-head">
                    <span class="set-card-ico ic-brand"><i class="fas fa-heart"></i></span>
                    <div class="sch-text">
                        <h3>The couple</h3>
                        <p>Who this story is about, and what to call it on the website.</p>
                    </div>
                </div>
                <div class="set-card-body">
                    <div class="field">
                        <div class="field-top">
                            <label for="title">Story title <span class="req" aria-hidden="true">*</span></label>
                            <span class="counter" data-counter data-max="150" data-ideal="90" for="title">0 / 150</span>
                        </div>
                        <input type="text" name="title" id="title" class="input" value="{{ old('title', $story->title) }}"
                               maxlength="150" required placeholder="e.g. From Twenty Minutes to a Lifetime">
                        <span class="hint">Shown as the headline everywhere the story appears. Keep it short enough to read on a phone.</span>
                    </div>

                    <div class="set-row-2" style="margin-top:18px">
                        <div class="field">
                            <label for="groom_name">Groom's name <span class="req" aria-hidden="true">*</span></label>
                            <input type="text" name="groom_name" id="groom_name" class="input"
                                   value="{{ old('groom_name', $story->groom_name) }}" maxlength="80" required>
                        </div>
                        <div class="field">
                            <label for="bride_name">Bride's name <span class="req" aria-hidden="true">*</span></label>
                            <input type="text" name="bride_name" id="bride_name" class="input"
                                   value="{{ old('bride_name', $story->bride_name) }}" maxlength="80" required>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ------------------------------ the story ------------------------------- --}}
            <section class="card set-card">
                <div class="set-card-head">
                    <span class="set-card-ico ic-accent"><i class="fas fa-quote-left"></i></span>
                    <div class="sch-text">
                        <h3>Their story</h3>
                        <p>The journey itself, plus where and when it happened.</p>
                    </div>
                </div>
                <div class="set-card-body">
                    <div class="field">
                        <div class="field-top">
                            <label for="story">Story <span class="req" aria-hidden="true">*</span></label>
                            <span class="counter" data-counter data-max="5000" data-ideal="1400" for="story">0 / 5000</span>
                        </div>
                        <textarea name="story" id="story" class="input" rows="11" maxlength="5000" required
                                  placeholder="How they met, what they got through, and why it worked out.">{{ old('story', $story->story) }}</textarea>
                        <span class="hint">Write it in plain paragraphs. A few hundred words reads best on the public page.</span>
                    </div>

                    <div class="set-row-2" style="margin-top:18px">
                        <div class="field">
                            <label for="location">Location</label>
                            <input type="text" name="location" id="location" class="input"
                                   value="{{ old('location', $story->location) }}" maxlength="120" placeholder="e.g. Dhaka, Bangladesh">
                            <span class="hint">Searchable, so visitors can find stories from their own area.</span>
                        </div>
                        <div class="field">
                            <label for="married_on">Marriage date</label>
                            <input type="date" name="married_on" id="married_on" class="input"
                                   value="{{ old('married_on', $story->married_on?->format('Y-m-d') ?? '') }}">
                            <span class="hint">Leave blank if you would rather not say.</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        {{-- ------------------------------- the sidebar ----------------------------- --}}
        <aside class="story-form-side">
            <section class="card set-card">
                <div class="set-card-head">
                    <span class="set-card-ico ic-success"><i class="fas fa-image"></i></span>
                    <div class="sch-text">
                        <h3>Cover photo</h3>
                        <p>The couple, if you have one.</p>
                    </div>
                </div>
                <div class="set-card-body">
                    <div class="field">
                        <input type="file" name="photo" id="photo" class="sr-only"
                               accept="image/jpeg,image/png,image/webp" data-preview="photoThumb">
                        <label class="uploader uploader-cover" for="photo" data-uploader="photo">
                            <span class="media-thumb" id="photoThumb">
                                @if ($photoUrl)
                                    <img src="{{ $photoUrl }}" alt="Current cover photo">
                                @else
                                    <i class="fas fa-image"></i>
                                @endif
                            </span>
                            <span class="uploader-info">
                                <span class="ui-name">{{ $photoUrl ? 'Replace photo' : 'Choose a photo' }}</span>
                                <span class="ui-file" data-file-name="photo">{{ $photoUrl ? 'Current photo is saved' : 'No photo chosen yet' }}</span>
                            </span>
                        </label>
                        <span class="hint">JPG, PNG or WEBP. Without one the couple's initials are shown instead.</span>
                    </div>
                </div>
            </section>

            <section class="card set-card">
                <div class="set-card-head">
                    <span class="set-card-ico ic-warning"><i class="fas fa-toggle-on"></i></span>
                    <div class="sch-text">
                        <h3>Publishing</h3>
                        <p>Who can see this, and how it is ranked.</p>
                    </div>
                </div>
                <div class="set-card-body">
                    <label class="switch-row">
                        <span class="switch">
                            <input type="checkbox" name="is_published" value="1"
                                   @checked(old('is_published', $story->is_published ?? false))>
                            <span class="track"></span>
                        </span>
                        <span class="switch-text">
                            <span class="sr-title">Published on the website</span>
                            <span class="hint">Visible to visitors on the public success-stories page.</span>
                        </span>
                    </label>

                    <label class="switch-row">
                        <span class="switch">
                            <input type="checkbox" name="is_featured" value="1"
                                   @checked(old('is_featured', $story->is_featured ?? false))>
                            <span class="track"></span>
                        </span>
                        <span class="switch-text">
                            <span class="sr-title">Featured story</span>
                            <span class="hint">Gets the crown badge and leads the featured stories.</span>
                        </span>
                    </label>

                    <div class="field" style="margin-top:18px">
                        <label for="sort_order">Sort order</label>
                        <input type="number" name="sort_order" id="sort_order" class="input"
                               value="{{ old('sort_order', $story->sort_order ?? 0) }}" min="0" max="999">
                        <span class="hint">Lower numbers come first. 0 puts this at the end.</span>
                    </div>
                </div>
            </section>

            @if ($isEditing)
                <section class="card set-card st-status-card">
                    <div class="set-card-body">
                        <div class="st-status-row">
                            <div>
                                <span class="st-status-k">Visibility</span>
                                <span class="st-status-v">
                                    @if ($story->is_published)
                                        <span class="badge badge-success"><i class="fas fa-circle-check"></i> Live</span>
                                    @else
                                        <span class="badge badge-muted"><i class="fas fa-eye-slash"></i> Draft</span>
                                    @endif
                                </span>
                            </div>
                            <a href="{{ route('success-stories.show', $story) }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">
                                <i class="fas fa-external-link"></i> View live
                            </a>
                        </div>
                    </div>
                </section>
            @endif
        </aside>
    </div>

    <div class="save-bar" data-save-bar>
        <div class="sb-state">
            <i class="fas fa-circle-check" data-sb-icon></i>
            <span data-sb-text data-sb-clean="{{ $isEditing ? 'No unsaved changes' : 'Fill in the couple and their story' }}">{{ $isEditing ? 'No unsaved changes' : 'Fill in the couple and their story' }}</span>
        </div>
        <div class="sb-actions">
            <a href="{{ route('backend.success-stories.index') }}" class="btn btn-ghost">Cancel</a>
            {{-- type=button, not reset: a reset button would wipe the form natively
                 the moment it is clicked, before the confirm below is answered. --}}
            <button type="button" class="btn btn-outline" data-reset-form hidden>Discard</button>
            <button class="btn btn-primary"><i class="fas fa-save"></i> {{ $isEditing ? 'Save Changes' : 'Create Story' }}</button>
        </div>
    </div>
</form>
