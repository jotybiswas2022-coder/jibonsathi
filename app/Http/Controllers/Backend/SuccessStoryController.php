<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\SuccessStoryRequest;
use App\Models\SuccessStory;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class SuccessStoryController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('manage', User::class);

        $filters = $request->validate([
            'status' => ['nullable', 'in:published,draft,featured,all'],
            'q' => ['nullable', 'string', 'max:80'],
        ]);

        $status = $filters['status'] ?? 'all';

        $stories = SuccessStory::query()
            ->when($status === 'published', fn ($q) => $q->where('is_published', true))
            ->when($status === 'draft', fn ($q) => $q->where('is_published', false))
            ->when($status === 'featured', fn ($q) => $q->where('is_featured', true))
            ->when(filled($filters['q'] ?? null), fn ($q) => $q->where(function ($inner) use ($filters) {
                $term = $filters['q'];
                $inner->where('title', 'like', '%'.$term.'%')
                    ->orWhere('groom_name', 'like', '%'.$term.'%')
                    ->orWhere('bride_name', 'like', '%'.$term.'%')
                    ->orWhere('location', 'like', '%'.$term.'%');
            }))
            ->orderBy('sort_order')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('backend.success-stories.index', [
            'stories' => $stories,
            'status' => $status,
            'filters' => $filters,
            'counts' => [
                'all' => SuccessStory::query()->count(),
                'published' => SuccessStory::query()->where('is_published', true)->count(),
                'draft' => SuccessStory::query()->where('is_published', false)->count(),
                'featured' => SuccessStory::query()->where('is_featured', true)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        Gate::authorize('manage', User::class);

        return view('backend.success-stories.create', ['story' => new SuccessStory]);
    }

    public function store(SuccessStoryRequest $request): RedirectResponse
    {
        $story = SuccessStory::create($this->payload($request));

        return redirect()
            ->route('backend.success-stories.index')
            ->with('success', "\"{$story->title}\" was created.");
    }

    public function edit(SuccessStory $story): View
    {
        Gate::authorize('manage', User::class);

        return view('backend.success-stories.edit', ['story' => $story]);
    }

    public function update(SuccessStoryRequest $request, SuccessStory $story): RedirectResponse
    {
        $story->update($this->payload($request, $story));

        return redirect()
            ->route('backend.success-stories.index')
            ->with('success', 'Success story updated.');
    }

    public function togglePublish(SuccessStory $story): RedirectResponse
    {
        Gate::authorize('manage', User::class);

        $story->update(['is_published' => ! $story->is_published]);

        return back()->with('success', $story->is_published
            ? 'Story published to the website.'
            : 'Story unpublished and hidden from the website.');
    }

    public function destroy(SuccessStory $story): RedirectResponse
    {
        Gate::authorize('manage', User::class);

        if ($story->photo_path) {
            Storage::disk('public')->delete($story->photo_path);
        }

        $story->delete();

        return back()->with('success', 'Success story deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(SuccessStoryRequest $request, ?SuccessStory $story = null): array
    {
        $data = [
            'title' => $request->validated('title'),
            'groom_name' => $request->validated('groom_name'),
            'bride_name' => $request->validated('bride_name'),
            'location' => $request->validated('location'),
            'story' => $request->validated('story'),
            'married_on' => $request->validated('married_on'),
            'is_published' => $request->boolean('is_published'),
            'is_featured' => $request->boolean('is_featured'),
            'sort_order' => (int) $request->input('sort_order', 0),
        ];

        if ($request->hasFile('photo')) {
            if ($story?->photo_path) {
                Storage::disk('public')->delete($story->photo_path);
            }

            $data['photo_path'] = $request->file('photo')->store('stories', 'public');
        } elseif ($request->boolean('remove_photo') && $story?->photo_path) {
            /* The form offers a Remove control for the current cover. Unlinking
               happens here rather than in the browser, so a pending removal can
               still be undone by discarding the form. */
            Storage::disk('public')->delete($story->photo_path);
            $data['photo_path'] = null;
        }

        return $data;
    }
}
