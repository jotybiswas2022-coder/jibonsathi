<?php

namespace App\Http\Controllers\Frontend\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\Profile\PhotoUploadRequest;
use App\Models\ProfilePhoto;
use App\Services\ProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PhotoSettingsController extends Controller
{
    public function __construct(private ProfileService $profiles)
    {
    }

    public function index(Request $request): View
    {
        return view('frontend.settings.photos', [
            'user' => $request->user()->load(['photos', 'primaryPhoto', 'profile']),
            'limit' => PhotoUploadRequest::MAX_PHOTOS,
        ]);
    }

    public function store(PhotoUploadRequest $request): RedirectResponse
    {
        if (! $request->withinGalleryLimit()) {
            return back()->withErrors(['photo' => 'You have reached the maximum of '.PhotoUploadRequest::MAX_PHOTOS.' photos. Remove one to add another.']);
        }

        $this->profiles->storePhoto(
            $request->user(),
            $request->file('photo'),
            $request->boolean('make_primary')
        );

        return back()->with('success', 'Photo uploaded successfully.');
    }

    public function primary(Request $request, ProfilePhoto $photo): RedirectResponse
    {
        Gate::authorize('update', $photo->user);

        $this->profiles->makePrimary($request->user(), $photo);

        return back()->with('success', 'Primary photo updated.');
    }

    public function destroy(Request $request, ProfilePhoto $photo): RedirectResponse
    {
        Gate::authorize('update', $photo->user);

        $this->profiles->deletePhoto($request->user(), $photo);

        return back()->with('success', 'Photo removed.');
    }
}
