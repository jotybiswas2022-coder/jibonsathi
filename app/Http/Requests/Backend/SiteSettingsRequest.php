<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class SiteSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:60'],
            'tagline' => ['nullable', 'string', 'max:120'],
            'contact_email' => ['required', 'email', 'max:120'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:160'],
            'facebook_url' => ['nullable', 'url', 'max:180'],
            'instagram_url' => ['nullable', 'url', 'max:180'],
            'twitter_url' => ['nullable', 'url', 'max:180'],
            'linkedin_url' => ['nullable', 'url', 'max:180'],
            'seo_title' => ['nullable', 'string', 'max:160'],
            'seo_description' => ['nullable', 'string', 'max:300'],
            'hero_headline' => ['nullable', 'string', 'max:160'],
            'hero_subheading' => ['nullable', 'string', 'max:300'],
            'footer_about' => ['nullable', 'string', 'max:500'],
            'privacy_policy' => ['nullable', 'string', 'max:20000'],
            'terms_conditions' => ['nullable', 'string', 'max:20000'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:png,ico,svg,jpg,jpeg', 'max:512'],
        ];
    }
}
