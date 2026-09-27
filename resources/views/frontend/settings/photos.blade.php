@extends('frontend.layouts.member')

@section('title', 'Photo Settings')

@section('member-content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Photos</h1>
            <p class="page-sub">Your first photo is the one members see first in search, so lead with a clear, recent picture of yourself.</p>
        </div>
        <div class="pm-counter">
            <span class="pm-counter-num">{{ $user->photos->count() }}<span class="pm-counter-of">/{{ $limit }}</span></span>
            <span class="pm-counter-label">Photos used</span>
        </div>
    </div>

    @include('frontend.settings.partials.nav', ['active' => 'photos'])

    <div class="pm-layout">
        <div class="pm-main">
            <section class="card card-pad pm-card">
                <div class="pm-head">
                    <h2 class="card-title"><i class="fas fa-images"></i> Your gallery</h2>
                    <span class="pm-head-note">The starred photo is your profile picture.</span>
                </div>

                <div class="progress pm-progress" role="presentation">
                    <span class="pm-progress-fill" style="width: {{ $limit ? round($user->photos->count() / $limit * 100) : 0 }}%"></span>
                </div>

                @if ($user->photos->isEmpty())
                    <x-frontend::empty-state :icon="'fa-image'" :title="'No photos yet'"
                        :description="'Add a clear, recent photo to help your profile stand out.'" />
                @else
                    <ul class="pm-grid">
                        @foreach ($user->photos as $photo)
                            {{-- The added date is a tooltip: at gallery-tile size the
                                 scrim belongs to the buttons, and "27 Sep 2026"
                                 beside two of them wraps onto three lines. --}}
                            <li class="pm-tile {{ $photo->is_primary ? 'is-primary' : '' }}"
                                @if ($photo->created_at) title="Added {{ $photo->created_at->format('j M Y') }}" @endif>
                                <img src="{{ \App\Support\Media::url($photo->path, $user->name) }}"
                                     alt="Photo {{ $loop->iteration }} of {{ $user->name }}" loading="lazy" decoding="async">

                                @if ($photo->is_primary)
                                    <span class="pm-tile-badge"><i class="fas fa-star"></i> Primary</span>
                                @endif

                                <div class="pm-tile-bar">
                                    <span class="pm-tile-actions">
                                        @if (! $photo->is_primary)
                                            <form method="POST" action="{{ route('settings.photos.primary', $photo) }}">
                                                @csrf @method('PUT')
                                                <button class="pm-act" title="Make this my primary photo"
                                                        aria-label="Make photo {{ $loop->iteration }} my primary photo">
                                                    <i class="fas fa-star"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('settings.photos.destroy', $photo) }}"
                                              data-confirm="Delete this photo? It will be removed from your profile straight away.">
                                            @csrf @method('DELETE')
                                            <button class="pm-act pm-act-danger" title="Delete this photo"
                                                    aria-label="Delete photo {{ $loop->iteration }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($user->photos->count() < $limit)
                    <form method="POST" action="{{ route('settings.photos.store') }}" enctype="multipart/form-data"
                          class="pm-upload">
                        @csrf

                        <label class="pm-drop" data-photo-drop>
                            <input type="file" name="photo" id="new-photo" class="pm-file"
                                   accept="image/jpeg,image/png,image/webp" required>
                            <span class="pm-drop-ico"><i class="fas fa-cloud-arrow-up"></i></span>
                            <span class="pm-drop-title">Choose a photo to upload</span>
                            <span class="pm-drop-sub">or drop one here</span>
                        </label>

                        <div class="pm-preview" data-photo-preview hidden>
                            <img src="" alt="" class="pm-preview-img" data-photo-img>
                            <div class="pm-preview-body">
                                <strong class="pm-preview-name" data-photo-name></strong>
                                <span class="pm-preview-note" data-photo-note></span>
                            </div>
                            <button type="button" class="pm-act pm-act-plain" data-photo-clear
                                    title="Choose a different photo" aria-label="Choose a different photo">
                                <i class="fas fa-xmark"></i>
                            </button>
                        </div>

                        @error('photo')
                            <div class="alert alert-danger pm-alert"><i class="fas fa-circle-exclamation"></i><span>{{ $message }}</span></div>
                        @enderror

                        @if ($user->photos->isNotEmpty())
                            <label class="switch-row pm-primary-switch">
                                <input type="checkbox" name="make_primary" value="1" @checked(old('make_primary'))>
                                <span class="switch"></span>
                                <span>
                                    <strong>Make this my primary photo</strong><br>
                                    <span class="text-tiny text-muted">Otherwise your current primary photo stays.</span>
                                </span>
                            </label>
                        @endif

                        <div class="pm-upload-actions">
                            <button class="btn btn-primary" data-photo-submit>Upload photo</button>
                            <span class="pm-upload-note">
                                <i class="fas fa-image"></i> JPG, PNG or WebP &middot; up to 4 MB &middot; at least 200&times;200
                            </span>
                        </div>
                    </form>
                @else
                    <p class="pm-full">
                        <i class="fas fa-circle-check"></i>
                        Your gallery is full at {{ $limit }} photos. Remove one to add another.
                    </p>
                @endif
            </section>
        </div>

        <aside class="pm-aside">
            <div class="pm-tips">
                <h2 class="pm-tips-title"><i class="fas fa-wand-magic-sparkles"></i> Photos that get replies</h2>
                <ul class="pm-tips-list">
                    <li>
                        <i class="fas fa-circle-check"></i>
                        <div>
                            <strong>Face clear, well lit</strong>
                            <span>Daylight near a window beats a flash or a dim room.</span>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-circle-check"></i>
                        <div>
                            <strong>Only you</strong>
                            <span>A recent solo photo, or one where you are clearly the subject.</span>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-circle-check"></i>
                        <div>
                            <strong>Nothing covering your face</strong>
                            <span>Skip sunglasses, heavy filters and cropped-out foreheads.</span>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-circle-xmark"></i>
                        <div>
                            <strong>Avoid these</strong>
                            <span>Group shots, blurry pictures, another couple, or anything too private.</span>
                        </div>
                    </li>
                </ul>
                <p class="pm-tips-foot">
                    Members with a photo get noticeably more interest, and the first one is the one shown
                    in search results and chat.
                </p>
            </div>
        </aside>
    </div>
@endsection
