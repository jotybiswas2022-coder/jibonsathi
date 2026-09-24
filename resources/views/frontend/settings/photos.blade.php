@extends('frontend.layouts.member')

@section('title', 'Photo Settings')

@section('member-content')
    <div class="page-head">
        <div>
            <h1 class="page-title">Settings</h1>
            <p class="page-sub">Manage your photos. Profiles with a photo get significantly more attention.</p>
        </div>
    </div>

    @include('frontend.settings.partials.nav', ['active' => 'photos'])

    <div class="card card-pad" style="max-width:860px">
        <h3 class="card-title"><i class="fas fa-images"></i> Your Photos ({{ $user->photos->count() }}/{{ $limit }})</h3>

        @if ($user->photos->isEmpty())
            <x-frontend::empty-state :icon="'fa-image'" :title="'No photos yet'"
                :description="'Add a clear, recent photo to help your profile stand out.'" />
        @else
            <div class="photo-manage">
                @foreach ($user->photos as $photo)
                    <div class="photo-tile {{ $photo->is_primary ? 'primary' : '' }}">
                        <img src="{{ \App\Support\Media::url($photo->path, $user->name) }}" alt="Profile photo">
                        @if ($photo->is_primary)
                            <span class="photo-tag">Primary</span>
                        @endif
                        <div class="photo-actions">
                            @if (! $photo->is_primary)
                                <form method="POST" action="{{ route('settings.photos.primary', $photo) }}">
                                    @csrf @method('PUT')
                                    <button class="btn btn-xs btn-soft" title="Make primary"><i class="fas fa-star"></i></button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('settings.photos.destroy', $photo) }}"
                                  data-confirm="Delete this photo?">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($user->photos->count() < $limit)
            <div style="margin-top:22px;border-top:1px solid var(--line);padding-top:18px">
                <h4 class="text-small" style="margin-bottom:6px;font-weight:700">Upload a new photo</h4>
                <form method="POST" action="{{ route('settings.photos.store') }}" enctype="multipart/form-data"
                      class="flex gap-2" style="gap:10px;align-items:center;flex-wrap:wrap">
                    @csrf
                    <input type="file" name="photo" id="new-photo" class="input" accept="image/jpeg,image/png,image/webp" required
                           style="max-width:340px;flex:1">
                    @error('photo') <span class="form-error">{{ $message }}</span> @enderror
                    <button class="btn btn-primary">Upload</button>
                </form>
                <p class="text-tiny text-muted mt-2">JPG, PNG or WebP up to 4 MB. Clear and appropriate photos are approved fastest.</p>
            </div>
        @endif
    </div>
@endsection