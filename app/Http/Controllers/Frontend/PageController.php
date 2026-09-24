<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\SiteSetting;
use App\Models\SuccessStory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function howItWorks(): View
    {
        return view('frontend.pages.how-it-works');
    }

    public function about(): View
    {
        return view('frontend.pages.about');
    }

    public function contact(): View
    {
        return view('frontend.pages.contact');
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:120'],
            'subject' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        Contact::create($data);

        return back()->with('success', 'Thanks for reaching out — our team will reply within one business day.');
    }

    public function privacy(): View
    {
        return view('frontend.pages.privacy', [
            'content' => SiteSetting::get('privacy_policy'),
        ]);
    }

    public function terms(): View
    {
        return view('frontend.pages.terms', [
            'content' => SiteSetting::get('terms_conditions'),
        ]);
    }
}
