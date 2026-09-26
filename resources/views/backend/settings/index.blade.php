@extends('backend.layouts.app')

@php
    use App\Support\Media;

    $val = fn (string $key, string $default = '') => old($key, $settings[$key] ?? ($defaults[$key] ?? $default));

    /* Per-field validation state, so a server error lands on the input it belongs
       to instead of only in the summary at the top of the page. */
    $err = fn (string $key) => $errors->has($key) ? ' error' : '';
    $errMsg = fn (string $key) => $errors->first($key);

    /* One entry per rail item. `required` lists the fields that must be filled for
       the section to count as ready, which drives the green dot in the rail. */
    $sections = [
        ['id' => 'general', 'icon' => 'fa-building', 'title' => 'General', 'desc' => 'Site name and the contact details visitors see.', 'required' => ['site_name', 'contact_email']],
        ['id' => 'social', 'icon' => 'fa-share-nodes', 'title' => 'Social Links', 'desc' => 'Profile links shown in the header and footer.', 'required' => []],
        ['id' => 'seo', 'icon' => 'fa-magnifying-glass', 'title' => 'SEO', 'desc' => 'How the site appears in Google search results.', 'required' => []],
        ['id' => 'homepage', 'icon' => 'fa-house', 'title' => 'Homepage', 'desc' => 'The hero banner at the top of the landing page.', 'required' => []],
        ['id' => 'legal', 'icon' => 'fa-scale-balanced', 'title' => 'Legal', 'desc' => 'Privacy policy and terms, shown as plain text.', 'required' => []],
        ['id' => 'branding', 'icon' => 'fa-palette', 'title' => 'Branding', 'desc' => 'Logo and favicon used across the whole site.', 'required' => []],
    ];

    $isReady = function (array $keys) use ($settings, $defaults): bool {
        foreach ($keys as $key) {
            $value = $settings[$key] ?? ($defaults[$key] ?? '');
            if (trim((string) $value) === '') {
                return false;
            }
        }
        return true;
    };
@endphp

@section('title', 'Site Settings')
@section('crumb', 'System · Branding, legal, and homepage content')

@section('content')
    {{-- novalidate: the form is long and saved from a sticky bar, so a silently
         blocked submit would look like nothing happened. Server-side validation
         repopulates the old input and renders the error summary below. --}}
    <form id="settingsForm" class="settings-form" method="POST" action="{{ route('backend.settings.update') }}" enctype="multipart/form-data" novalidate
          data-confirm-title="Save these settings?"
          data-confirm="The changes go live for every visitor immediately."
          data-confirm-ok="Save settings" data-confirm-icon="question"
          data-confirm-color="#8B1E3F" data-confirm-focus-cancel>
        @csrf

        <div class="settings-shell">
            {{-- Section rail: jump between groups instead of scrolling the whole page --}}
            <nav class="set-nav" aria-label="Settings sections">
                <div class="set-nav-label">Sections</div>
                @foreach ($sections as $section)
                    {{-- data-set-link carries the card's DOM id, so the JS can find
                         the section directly and animate the jump. --}}
                    <a href="#set-{{ $section['id'] }}" data-set-link="set-{{ $section['id'] }}"
                       class="{{ $loop->first ? 'active' : '' }}">
                        <i class="fas {{ $section['icon'] }}"></i>
                        <span>{{ $section['title'] }}</span>
                        @if ($section['required'])
                            <em class="dot {{ $isReady($section['required']) ? 'ready' : '' }}"
                                title="{{ $isReady($section['required']) ? 'All required fields are filled' : 'Required fields are missing' }}"></em>
                        @endif
                    </a>
                @endforeach
                <div class="set-nav-note">
                    <i class="fas fa-circle-info"></i>
                    <span>Everything here goes live for every visitor the moment you save.</span>
                </div>
            </nav>

            <div class="set-panel">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-circle-exclamation"></i>
                        <div>
                            <strong>Please fix {{ $errors->count() }} {{ Str::plural('problem', $errors->count()) }} below:</strong>
                            <ul style="margin:6px 0 0;padding-left:18px">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
                        </div>
                    </div>
                @endif

                {{-- ------------------------------- General ------------------------------- --}}
                <section class="card set-card" id="set-general">
                    <div class="set-card-head">
                        <span class="set-card-ico ic-brand"><i class="fas fa-building"></i></span>
                        <div class="sch-text">
                            <h3>General</h3>
                            <p>Site name and the contact details visitors see.</p>
                        </div>
                    </div>
                    <div class="set-card-body">
                        <div class="set-row">
                            <div class="field">
                                <div class="field-top">
                                    <label for="site_name">Site Name <span class="req">*</span></label>
                                    <span class="counter" data-counter data-max="60" data-ideal="60" for="site_name">0 / 60</span>
                                </div>
                                <input type="text" name="site_name" id="site_name" class="input{{ $err('site_name') }}" maxlength="60"
                                       value="{{ $val('site_name') }}" required>
                                @if ($errors->has('site_name'))<span class="form-error"><i class="fas fa-circle-exclamation"></i> {{ $errMsg('site_name') }}</span>@else
                                <span class="hint">Used in the browser tab, the header and outgoing email.</span>@endif
                            </div>
                            <div class="field">
                                <div class="field-top">
                                    <label for="tagline">Tagline</label>
                                    <span class="counter" data-counter data-max="120" data-ideal="120" for="tagline">0 / 120</span>
                                </div>
                                <input type="text" name="tagline" id="tagline" class="input{{ $err('tagline') }}" maxlength="120" value="{{ $val('tagline') }}">
                                @if ($errors->has('tagline'))<span class="form-error"><i class="fas fa-circle-exclamation"></i> {{ $errMsg('tagline') }}</span>@endif
                            </div>
                        </div>

                        <div class="set-row">
                            <div class="field">
                                <div class="field-top">
                                    <label for="contact_email">Contact Email <span class="req">*</span></label>
                                </div>
                                <div class="field-icon">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email" name="contact_email" id="contact_email" class="input{{ $err('contact_email') }}" maxlength="120"
                                           value="{{ $val('contact_email') }}" required>
                                </div>
                                @if ($errors->has('contact_email'))<span class="form-error"><i class="fas fa-circle-exclamation"></i> {{ $errMsg('contact_email') }}</span>@else
                                <span class="hint">Shown in the footer and used for reply-to.</span>@endif
                            </div>
                            <div class="field">
                                <div class="field-top">
                                    <label for="contact_phone">Contact Phone</label>
                                </div>
                                <div class="field-icon">
                                    <i class="fas fa-phone"></i>
                                    <input type="text" name="contact_phone" id="contact_phone" class="input{{ $err('contact_phone') }}" maxlength="30"
                                           value="{{ $val('contact_phone') }}">
                                </div>
                                @if ($errors->has('contact_phone'))<span class="form-error"><i class="fas fa-circle-exclamation"></i> {{ $errMsg('contact_phone') }}</span>@endif
                            </div>
                        </div>

                        <div class="field">
                            <div class="field-top">
                                <label for="address">Address</label>
                                <span class="counter" data-counter data-max="160" data-ideal="160" for="address">0 / 160</span>
                            </div>
                            <input type="text" name="address" id="address" class="input{{ $err('address') }}" maxlength="160" value="{{ $val('address') }}">
                            @if ($errors->has('address'))<span class="form-error"><i class="fas fa-circle-exclamation"></i> {{ $errMsg('address') }}</span>@endif
                        </div>
                    </div>
                </section>

                {{-- ---------------------------- Social links ---------------------------- --}}
                <section class="card set-card" id="set-social">
                    <div class="set-card-head">
                        <span class="set-card-ico ic-info"><i class="fas fa-share-nodes"></i></span>
                        <div class="sch-text">
                            <h3>Social Links</h3>
                            <p>Profile links shown in the header and footer.</p>
                        </div>
                    </div>
                    <div class="set-card-body">
                        <div class="set-row">
                            @foreach ([
                                ['facebook_url', 'Facebook', 'fab fa-facebook', 'social-ic-facebook'],
                                ['instagram_url', 'Instagram', 'fab fa-instagram', 'social-ic-instagram'],
                                ['twitter_url', 'X / Twitter', 'fab fa-x-twitter', 'social-ic-twitter'],
                                ['linkedin_url', 'LinkedIn', 'fab fa-linkedin-in', 'social-ic-linkedin'],
                            ] as [$key, $label, $icon, $color])
                                <div class="field">
                                    <div class="field-top">
                                        <label for="{{ $key }}">{{ $label }}</label>
                                    </div>
                                    <div class="field-icon">
                                        <i class="{{ $icon }} {{ $color }}"></i>
                                        <input type="url" name="{{ $key }}" id="{{ $key }}" class="input{{ $err($key) }}"
                                               maxlength="180" value="{{ $val($key) }}" placeholder="https://"
                                               data-clearable>
                                        <button type="button" class="clear-x" data-clear="{{ $key }}"
                                                aria-label="Clear {{ $label }} link" tabindex="-1"
                                                @if (! $val($key)) hidden @endif>
                                            <i class="fas fa-xmark"></i>
                                        </button>
                                    </div>
                                    @if ($errors->has($key))<span class="form-error"><i class="fas fa-circle-exclamation"></i> {{ $errMsg($key) }}</span>@endif
                                </div>
                            @endforeach
                        </div>
                        <p class="hint mb-0">Leave a field empty to hide that icon. Add <code>https://</code> or the link will not be accepted.</p>
                    </div>
                </section>

                {{-- --------------------------------- SEO ---------------------------------- --}}
                <section class="card set-card" id="set-seo">
                    <div class="set-card-head">
                        <span class="set-card-ico ic-accent"><i class="fas fa-magnifying-glass"></i></span>
                        <div class="sch-text">
                            <h3>SEO</h3>
                            <p>How the site appears in Google search results.</p>
                        </div>
                    </div>
                    <div class="set-card-body">
                        <div class="field">
                            <div class="field-top">
                                <label for="seo_title">Meta Title</label>
                                <span class="counter" data-counter data-max="160" data-ideal="60" for="seo_title">0 / 160</span>
                            </div>
                            <input type="text" name="seo_title" id="seo_title" class="input{{ $err('seo_title') }}" maxlength="160" value="{{ $val('seo_title') }}">
                            <span class="hint">Around 60 characters displays best. Google cuts anything longer.</span>
                            @if ($errors->has('seo_title'))<span class="form-error"><i class="fas fa-circle-exclamation"></i> {{ $errMsg('seo_title') }}</span>@endif
                        </div>

                        <div class="field">
                            <div class="field-top">
                                <label for="seo_description">Meta Description</label>
                                <span class="counter" data-counter data-max="300" data-ideal="160" for="seo_description">0 / 300</span>
                            </div>
                            <textarea name="seo_description" id="seo_description" class="input{{ $err('seo_description') }}" rows="3" maxlength="300">{{ $val('seo_description') }}</textarea>
                            <span class="hint">Around 160 characters. This is the grey snippet under your link in Google.</span>
                            @if ($errors->has('seo_description'))<span class="form-error"><i class="fas fa-circle-exclamation"></i> {{ $errMsg('seo_description') }}</span>@endif
                        </div>

                        {{-- Live preview of the search result --}}
                        <div class="serp">
                            <div class="serp-label"><i class="fas fa-eye"></i> Search result preview</div>
                            <div class="serp-url">
                                <i class="fas fa-lock"></i>
                                <span data-serp-url>{{ $defaults['site_name'] ?? config('app.name') }} — jibonsathi</span>
                            </div>
                            <div class="serp-title" data-serp-title>{{ $val('seo_title') ?: ($defaults['seo_title'] ?? $defaults['site_name'] ?? config('app.name')) }}</div>
                            <div class="serp-desc" data-serp-desc>{{ $val('seo_description') ?: 'Add a meta description to control the grey snippet Google shows here.' }}</div>
                        </div>
                    </div>
                </section>

                {{-- ------------------------------ Homepage ------------------------------- --}}
                <section class="card set-card" id="set-homepage">
                    <div class="set-card-head">
                        <span class="set-card-ico ic-success"><i class="fas fa-house"></i></span>
                        <div class="sch-text">
                            <h3>Homepage Content</h3>
                            <p>The hero banner at the top of the landing page.</p>
                        </div>
                    </div>
                    <div class="set-card-body">
                        <div class="field">
                            <div class="field-top">
                                <label for="hero_headline">Hero Headline</label>
                                <span class="counter" data-counter data-max="160" data-ideal="60" for="hero_headline">0 / 160</span>
                            </div>
                            <input type="text" name="hero_headline" id="hero_headline" class="input{{ $err('hero_headline') }}" maxlength="160" value="{{ $val('hero_headline') }}">
                            @if ($errors->has('hero_headline'))<span class="form-error"><i class="fas fa-circle-exclamation"></i> {{ $errMsg('hero_headline') }}</span>@endif
                        </div>

                        <div class="field">
                            <div class="field-top">
                                <label for="hero_subheading">Hero Subheading</label>
                                <span class="counter" data-counter data-max="300" data-ideal="160" for="hero_subheading">0 / 300</span>
                            </div>
                            <textarea name="hero_subheading" id="hero_subheading" class="input{{ $err('hero_subheading') }}" rows="3" maxlength="300">{{ $val('hero_subheading') }}</textarea>
                            @if ($errors->has('hero_subheading'))<span class="form-error"><i class="fas fa-circle-exclamation"></i> {{ $errMsg('hero_subheading') }}</span>@endif
                        </div>

                        <div class="field">
                            <div class="field-top">
                                <label for="footer_about">Footer About Text</label>
                                <span class="counter" data-counter data-max="500" data-ideal="200" for="footer_about">0 / 500</span>
                            </div>
                            <textarea name="footer_about" id="footer_about" class="input{{ $err('footer_about') }}" rows="3" maxlength="500">{{ $val('footer_about') }}</textarea>
                            @if ($errors->has('footer_about'))<span class="form-error"><i class="fas fa-circle-exclamation"></i> {{ $errMsg('footer_about') }}</span>@endif
                        </div>

                        {{-- Live preview of the hero --}}
                        <div class="hero-pv">
                            <div class="hp-eyebrow"><i class="fas fa-heart"></i> <span data-hero-brand>{{ $val('site_name') ?: ($defaults['site_name'] ?? 'Jibon Sathi') }}</span></div>
                            <h3 data-hero-headline>{{ $val('hero_headline') ?: ($defaults['hero_headline'] ?? 'Find the life you were meant for') }}</h3>
                            <p data-hero-sub>{{ $val('hero_subheading') ?: ($defaults['hero_subheading'] ?? 'Tell us about yourself and let verified matches come to you.') }}</p>
                            <div class="hp-brand">
                                @if (! empty($settings['logo_path']))
                                    <img src="{{ Media::url($settings['logo_path']) }}" alt="">
                                @endif
                                <span data-hero-tagline>{{ $val('tagline') ?: ($defaults['tagline'] ?? '') }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- -------------------------------- Legal --------------------------------- --}}
                <section class="card set-card" id="set-legal">
                    <div class="set-card-head">
                        <span class="set-card-ico ic-muted"><i class="fas fa-scale-balanced"></i></span>
                        <div class="sch-text">
                            <h3>Legal Pages</h3>
                            <p>Privacy policy and terms, shown as plain text.</p>
                        </div>
                    </div>
                    <div class="set-card-body">
                        <div class="field">
                            <div class="field-top">
                                <label for="privacy_policy">Privacy Policy</label>
                                <span class="counter" data-counter data-max="20000" for="privacy_policy">0 / 20000</span>
                            </div>
                            <textarea name="privacy_policy" id="privacy_policy" class="input{{ $err('privacy_policy') }}" rows="10" maxlength="20000">{{ $val('privacy_policy') }}</textarea>
                            <span class="hint">Plain text only — HTML is not rendered here.</span>
                            @if ($errors->has('privacy_policy'))<span class="form-error"><i class="fas fa-circle-exclamation"></i> {{ $errMsg('privacy_policy') }}</span>@endif
                        </div>

                        <div class="field">
                            <div class="field-top">
                                <label for="terms_conditions">Terms &amp; Conditions</label>
                                <span class="counter" data-counter data-max="20000" for="terms_conditions">0 / 20000</span>
                            </div>
                            <textarea name="terms_conditions" id="terms_conditions" class="input{{ $err('terms_conditions') }}" rows="10" maxlength="20000">{{ $val('terms_conditions') }}</textarea>
                            @if ($errors->has('terms_conditions'))<span class="form-error"><i class="fas fa-circle-exclamation"></i> {{ $errMsg('terms_conditions') }}</span>@endif
                        </div>
                    </div>
                </section>

                {{-- ------------------------------ Branding ------------------------------- --}}
                <section class="card set-card" id="set-branding">
                    <div class="set-card-head">
                        <span class="set-card-ico ic-warning"><i class="fas fa-palette"></i></span>
                        <div class="sch-text">
                            <h3>Branding</h3>
                            <p>Logo and favicon used across the whole site.</p>
                        </div>
                    </div>
                    <div class="set-card-body">
                        <div class="set-row">
                            <div class="field">
                                <div class="field-top"><label for="logo">Site Logo</label></div>
                                <input type="file" name="logo" id="logo" class="sr-only" accept="image/png,image/jpeg,image/svg+xml,image/webp" data-preview="logoThumb">
                                <label class="uploader" for="logo" data-uploader="logo">
                                    <span class="media-thumb" id="logoThumb">
                                        @if (! empty($settings['logo_path']))
                                            <img src="{{ Media::url($settings['logo_path']) }}" alt="Current logo">
                                        @else
                                            <i class="fas fa-image"></i>
                                        @endif
                                    </span>
                                    <span class="uploader-info">
                                        <span class="ui-name">Site Logo</span>
                                        <span class="ui-file" data-file-name="logo">{{ ! empty($settings['logo_path']) ? 'Current logo is saved' : 'No logo uploaded yet' }}</span>
                                        <span class="btn btn-outline btn-sm"><i class="fas fa-upload"></i> Choose file</span>
                                    </span>
                                </label>
                                <span class="hint">PNG, JPG, SVG or WEBP · up to 2 MB. Replaces the current logo.</span>
                            </div>

                            <div class="field">
                                <div class="field-top"><label for="favicon">Favicon</label></div>
                                <input type="file" name="favicon" id="favicon" class="sr-only" accept="image/x-icon,image/png,image/svg+xml,image/jpeg" data-preview="faviconThumb">
                                <label class="uploader" for="favicon" data-uploader="favicon">
                                    <span class="media-thumb" id="faviconThumb">
                                        @if (! empty($settings['favicon_path']))
                                            <img src="{{ Media::url($settings['favicon_path']) }}" alt="Current favicon">
                                        @else
                                            <i class="fas fa-star"></i>
                                        @endif
                                    </span>
                                    <span class="uploader-info">
                                        <span class="ui-name">Favicon</span>
                                        <span class="ui-file" data-file-name="favicon">{{ ! empty($settings['favicon_path']) ? 'Current favicon is saved' : 'No favicon uploaded yet' }}</span>
                                        <span class="btn btn-outline btn-sm"><i class="fas fa-upload"></i> Choose file</span>
                                    </span>
                                </label>
                                <span class="hint">PNG, ICO, SVG or JPG · up to 512 KB. Shown in the browser tab.</span>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Sticky action bar, so saving never needs a full-page scroll --}}
                <div class="save-bar" data-save-bar>
                    <div class="sb-state">
                        <i class="fas fa-circle-check" data-sb-icon></i>
                        <span data-sb-text>All changes saved</span>
                    </div>
                    <div class="sb-actions">
                        <button type="reset" class="btn btn-ghost" data-reset-form hidden>Discard</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save All Settings</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
