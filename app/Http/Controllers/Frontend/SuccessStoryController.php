<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\SuccessStory;
use Illuminate\Contracts\View\View;

class SuccessStoryController extends Controller
{
    public function index(): View
    {
        return view('frontend.success-stories.index', [
            'stories' => SuccessStory::query()
                ->published()
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->latest('married_on')
                ->paginate(9),
        ]);
    }

    public function show(SuccessStory $story): View
    {
        abort_unless($story->is_published || (bool) auth()->user()?->is_admin, 404);

        $related = SuccessStory::query()
            ->published()
            ->whereKeyNot($story->id)
            ->latest('married_on')
            ->limit(3)
            ->get();

        return view('frontend.success-stories.show', compact('story', 'related'));
    }
}
