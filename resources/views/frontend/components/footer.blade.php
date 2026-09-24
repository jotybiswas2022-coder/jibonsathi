@php($settings = \App\Models\SiteSetting::all_cached())
@php($siteName = $settings['site_name'] ?? 'Jibon Sathi')
<footer class="footer">
    <div class="container">
        <div class="footer-top">
            <div>
                <a href="{{ route('home') }}" class="brand">
                    <span class="brand-mark gold"><i class="fas fa-heart"></i></span>
                    {{ $siteName }}
                </a>
                <p>{{ $settings['footer_about'] ?? 'Jibon Sathi is a free matrimony platform built on trust, privacy and genuine profiles — helping two lives come together meaningfully.' }}</p>
                <div class="social-row">
                    @if (!empty($settings['facebook_url']))<a href="{{ $settings['facebook_url'] }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>@endif
                    @if (!empty($settings['instagram_url']))<a href="{{ $settings['instagram_url'] }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>@endif
                    @if (!empty($settings['twitter_url']))<a href="{{ $settings['twitter_url'] }}" target="_blank" rel="noopener" aria-label="Twitter / X"><i class="fab fa-x-twitter"></i></a>@endif
                    @if (!empty($settings['linkedin_url']))<a href="{{ $settings['linkedin_url'] }}" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>@endif
                </div>
            </div>

            <div>
                <h4>Platform</h4>
                <div class="footer-links">
                    <a href="{{ route('discover.index') }}">Discover Profiles</a>
                    @auth
                        <a href="{{ route('matches.index') }}">My Matches</a>
                        <a href="{{ route('favorites.index') }}">My Shortlist</a>
                        <a href="{{ route('verification.index') }}">Get Verified</a>
                    @endauth
                </div>
            </div>

            <div>
                <h4>Company</h4>
                <div class="footer-links">
                    <a href="{{ route('pages.about') }}">About Us</a>
                    <a href="{{ route('pages.how-it-works') }}">How It Works</a>
                    <a href="{{ route('success-stories.index') }}">Success Stories</a>
                    <a href="{{ route('pages.contact') }}">Contact</a>
                </div>
            </div>

            <div>
                <h4>Get In Touch</h4>
                <div class="footer-links">
                    @if (!empty($settings['contact_email']))<a href="mailto:{{ $settings['contact_email'] }}"><i class="fas fa-envelope"></i> {{ $settings['contact_email'] }}</a>@endif
                    @if (!empty($settings['contact_phone']))<a href="tel:{{ $settings['contact_phone'] }}"><i class="fas fa-phone"></i> {{ $settings['contact_phone'] }}</a>@endif
                    @if (!empty($settings['address']))<span><i class="fas fa-location-dot"></i> {{ $settings['address'] }}</span>@endif
                    <a href="{{ route('pages.privacy') }}"><i class="fas fa-shield-halved"></i> Privacy Policy</a>
                    <a href="{{ route('pages.terms') }}"><i class="fas fa-file-contract"></i> Terms & Conditions</a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <span>&copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.</span>
            <span>Made with <i class="fas fa-heart" style="color:var(--accent)"></i> for meaningful connections.</span>
        </div>
    </div>
</footer>