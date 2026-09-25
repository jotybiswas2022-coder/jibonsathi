@extends('backend.layouts.app')

@php
    $val = fn (string $key, string $default = '') => old($key, $settings[$key] ?? ($defaults[$key] ?? $default));
@endphp

@section('title', 'Site Settings')
@section('crumb', 'System · Branding, legal, and homepage content')

@section('content')
    @if (session('success'))
        <div class="alert alert-success" data-auto-close><i class="fas fa-circle-check"></i> {{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('backend.settings.update') }}" enctype="multipart/form-data" style="max-width:940px"
          data-confirm-title="Save these settings?"
          data-confirm="The changes go live for every visitor immediately."
          data-confirm-ok="Save settings" data-confirm-icon="question"
          data-confirm-color="#8B1E3F" data-confirm-focus-cancel>
        @csrf
        @method('POST')

        @if ($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-circle-exclamation"></i>
                <div>
                    Please fix the errors below:
                    <ul style="margin:6px 0 0;padding-left:18px">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
                </div>
            </div>
        @endif

        <div class="card card-pad">
            <div class="section-block-title"><i class="fas fa-building"></i> General</div>
            <div class="field-group">
                <div class="field">
                    <label for="site_name">Site Name</label>
                    <input type="text" name="site_name" id="site_name" class="input" maxlength="60" value="{{ $val('site_name') }}" required>
                </div>
                <div class="field">
                    <label for="tagline">Tagline</label>
                    <input type="text" name="tagline" id="tagline" class="input" maxlength="120" value="{{ $val('tagline') }}">
                </div>
            </div>
            <div class="field-group">
                <div class="field">
                    <label for="contact_email">Contact Email</label>
                    <input type="email" name="contact_email" id="contact_email" class="input" maxlength="120" value="{{ $val('contact_email') }}" required>
                </div>
                <div class="field">
                    <label for="contact_phone">Contact Phone</label>
                    <input type="text" name="contact_phone" id="contact_phone" class="input" maxlength="30" value="{{ $val('contact_phone') }}">
                </div>
            </div>
            <div class="field">
                <label for="address">Address</label>
                <input type="text" name="address" id="address" class="input" maxlength="160" value="{{ $val('address') }}">
            </div>
        </div>

        <div class="card card-pad" style="margin-top:20px">
            <div class="section-block-title"><i class="fas fa-brands"></i> Social Links</div>
            <div class="field-group">
                <div class="field">
                    <label for="facebook_url">Facebook</label>
                    <input type="url" name="facebook_url" id="facebook_url" class="input" value="{{ $val('facebook_url') }}">
                </div>
                <div class="field">
                    <label for="instagram_url">Instagram</label>
                    <input type="url" name="instagram_url" id="instagram_url" class="input" value="{{ $val('instagram_url') }}">
                </div>
                <div class="field">
                    <label for="twitter_url">X / Twitter</label>
                    <input type="url" name="twitter_url" id="twitter_url" class="input" value="{{ $val('twitter_url') }}">
                </div>
                <div class="field">
                    <label for="linkedin_url">LinkedIn</label>
                    <input type="url" name="linkedin_url" id="linkedin_url" class="input" value="{{ $val('linkedin_url') }}">
                </div>
            </div>
        </div>

        <div class="card card-pad" style="margin-top:20px">
            <div class="section-block-title"><i class="fas fa-magnifying-glass"></i> SEO</div>
            <div class="field">
                <label for="seo_title">Meta Title</label>
                <input type="text" name="seo_title" id="seo_title" class="input" maxlength="160" value="{{ $val('seo_title') }}">
            </div>
            <div class="field">
                <label for="seo_description">Meta Description</label>
                <textarea name="seo_description" id="seo_description" class="input" rows="2" maxlength="300">{{ $val('seo_description') }}</textarea>
            </div>
        </div>

        <div class="card card-pad" style="margin-top:20px">
            <div class="section-block-title"><i class="fas fa-house"></i> Homepage Content</div>
            <div class="field">
                <label for="hero_headline">Hero Headline</label>
                <input type="text" name="hero_headline" id="hero_headline" class="input" maxlength="160" value="{{ $val('hero_headline') }}">
            </div>
            <div class="field">
                <label for="hero_subheading">Hero Subheading</label>
                <textarea name="hero_subheading" id="hero_subheading" class="input" rows="2" maxlength="300">{{ $val('hero_subheading') }}</textarea>
            </div>
            <div class="field">
                <label for="footer_about">Footer About Text</label>
                <textarea name="footer_about" id="footer_about" class="input" rows="3" maxlength="500">{{ $val('footer_about') }}</textarea>
            </div>
        </div>

        <div class="card card-pad" style="margin-top:20px">
            <div class="section-block-title"><i class="fas fa-scale-balanced"></i> Legal Pages</div>
            <div class="field">
                <label for="privacy_policy">Privacy Policy (plain text)</label>
                <textarea name="privacy_policy" id="privacy_policy" class="input" rows="10" maxlength="20000">{{ $val('privacy_policy') }}</textarea>
            </div>
            <div class="field">
                <label for="terms_conditions">Terms &amp; Conditions (plain text)</label>
                <textarea name="terms_conditions" id="terms_conditions" class="input" rows="10" maxlength="20000">{{ $val('terms_conditions') }}</textarea>
            </div>
        </div>

        <div class="card card-pad" style="margin-top:20px">
            <div class="section-block-title"><i class="fas fa-palette"></i> Branding</div>
            <div class="field-group">
                <div class="field">
                    <label for="logo">Logo Upload</label>
                    <input type="file" name="logo" id="logo" class="input" accept="image/png,image/jpeg,image/svg+xml,image/webp">
                    @if (! empty($settings['logo_path']))
                        <img src="{{ \App\Support\Media::url($settings['logo_path']) }}" alt="Current logo" style="max-height:48px;margin-top:8px">
                    @endif
                </div>
                <div class="field">
                    <label for="favicon">Favicon Upload</label>
                    <input type="file" name="favicon" id="favicon" class="input" accept="image/x-icon,image/png,image/svg+xml,image/jpeg">
                    @if (! empty($settings['favicon_path']))
                        <img src="{{ \App\Support\Media::url($settings['favicon_path']) }}" alt="Current favicon" style="max-height:32px;margin-top:8px">
                    @endif
                </div>
            </div>
        </div>

        <div style="margin-top:20px;display:flex;justify-content:flex-end">
            <button class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save All Settings</button>
        </div>
    </form>
@endsection