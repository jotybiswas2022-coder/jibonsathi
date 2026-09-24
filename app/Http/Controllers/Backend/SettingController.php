<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\SiteSettingsRequest;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /** Keys the admin panel can write. */
    private const EDITABLE = [
        'site_name', 'tagline', 'contact_email', 'contact_phone', 'address',
        'facebook_url', 'instagram_url', 'twitter_url', 'linkedin_url',
        'seo_title', 'seo_description', 'hero_headline', 'hero_subheading',
        'footer_about', 'privacy_policy', 'terms_conditions',
    ];

    public function edit(): View
    {
        Gate::authorize('manage', User::class);

        return view('backend.settings.index', [
            'settings' => collect(SiteSetting::all_cached())
                ->mapWithKeys(fn ($value, $key) => [$key => $value === '' ? (SiteSetting::DEFAULTS[$key] ?? '') : $value])
                ->all(),
            'defaults' => SiteSetting::DEFAULTS,
        ]);
    }

    public function update(SiteSettingsRequest $request): RedirectResponse
    {
        Gate::authorize('manage', User::class);

        $validated = $request->validated();

        foreach (self::EDITABLE as $key) {
            if ($request->has($key)) {
                SiteSetting::put($key, (string) ($validated[$key] ?? ''), $this->groupFor($key));
            }
        }

        foreach (['logo' => 'logo_path', 'favicon' => 'favicon_path'] as $input => $key) {
            if ($request->hasFile($input)) {
                $existing = SiteSetting::get($key);

                if ($existing && Storage::disk('public')->exists($existing)) {
                    Storage::disk('public')->delete($existing);
                }

                SiteSetting::put($key, $request->file($input)->store('branding', 'public'), 'branding');
            }
        }

        SiteSetting::flush();

        return back()->with('success', 'Site settings saved.');
    }

    private function groupFor(string $key): string
    {
        return match (true) {
            in_array($key, ['facebook_url', 'instagram_url', 'twitter_url', 'linkedin_url'], true) => 'social',
            in_array($key, ['seo_title', 'seo_description'], true) => 'seo',
            in_array($key, ['hero_headline', 'hero_subheading', 'footer_about'], true) => 'homepage',
            in_array($key, ['privacy_policy', 'terms_conditions'], true) => 'legal',
            default => 'general',
        };
    }
}
