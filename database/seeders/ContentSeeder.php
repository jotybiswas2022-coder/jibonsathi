<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\SiteSetting;
use App\Models\SuccessStory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Success stories (with generated cover art), the platform's default
 * site settings, and a couple of sample contact messages.
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $this->seedStories();
        $this->seedContacts();
    }

    private function seedSettings(): void
    {
        $settings = [
            'site_name' => ['Jora', 'general'],
            'tagline' => ['Bringing Two Lives Together.', 'general'],
            'contact_email' => ['hello@jora.example', 'general'],
            'contact_phone' => ['+880 1700 000000', 'general'],
            'address' => ['Gulshan Avenue, Dhaka, Bangladesh', 'general'],
            'facebook_url' => ['https://facebook.com', 'social'],
            'instagram_url' => ['https://instagram.com', 'social'],
            'twitter_url' => ['https://twitter.com', 'social'],
            'linkedin_url' => ['https://linkedin.com', 'social'],
            'seo_title' => ['Jora — Free Matrimony for Meaningful Marriages', 'seo'],
            'seo_description' => ['Jora is a completely free matrimony platform connecting serious, verified profiles who are ready to build a life together.', 'seo'],
            'hero_headline' => ['Find Someone Who Complements Your Life', 'content'],
            'hero_subheading' => ['Meaningful connections, genuine profiles, and a better way to find your life partner.', 'content'],
            'footer_about' => ['Jora is a free, privacy-first matrimony platform built for people who are serious about finding a life partner.', 'content'],
            'privacy_policy' => [<<<'TEXT'
<h2>Privacy at a glance</h2>
<p>Your privacy matters to us. This page explains what information Jora collects and how we use it to help you find a life partner.</p>
<h3>What we collect</h3>
<p>When you create a profile we collect the details you choose to share — name, contact details, and your personal, family and lifestyle information. We also log basic usage data so the service performs well.</p>
<h3>How we use it</h3>
<p>Your information is used only to build your profile, generate matches, and let you communicate with other members. We never sell your data.</p>
<h3>Your control</h3>
<p>You decide what is visible on your profile, who can message you, and who can view you. You can update or delete this at any time from your settings.</p>
<h3>Contact</h3>
<p>Questions about privacy? Write to us and we will gladly help.</p>
TEXT, 'legal'],
            'terms_conditions' => [<<<'TEXT'
<h2>Terms of service</h2>
<p>By using Jora you agree to these simple terms.</p>
<h3>Eligibility</h3>
<p>You must be of legal marriageable age and genuinely looking for a life partner. All members are expected to be truthful in their profiles.</p>
<h3>Acceptable use</h3>
<p>Be kind. Do not post false information, harass others, or use the platform for anything other than finding a suitable match.</p>
<h3>Accounts</h3>
<p>You are responsible for keeping your login details safe. If you misuse the platform your profile may be suspended.</p>
<h3>Changes</h3>
<p>We may update these terms over time. Continued use of the platform means you accept the latest version.</p>
TEXT, 'legal'],
        ];

        foreach ($settings as $key => [$value, $group]) {
            SiteSetting::put($key, $value, $group);
        }
    }

    private function seedStories(): void
    {
        if (SuccessStory::query()->exists()) {
            return;
        }

        $stories = [
            [
                'title' => 'From Twenty Minutes to a Lifetime',
                'groom_name' => 'Arif Hasan',
                'bride_name' => 'Nusrat Jahan',
                'location' => 'Dhaka',
                'story' => 'Arif had almost given up searching when he came across Nusrat\u2019s profile. Her headline about quiet evenings spoke directly to him. They exchanged messages for a month before meeting, and twenty minutes into that first conversation, both knew. Eight months later their families celebrated an engagement that began with a simple click.',
                'married_on' => '2025-11-18',
                'photo_path' => 'seed-photos/story-1.svg',
                'is_published' => true,
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Matched by Values, Not Algorithms Alone',
                'groom_name' => 'Tanvir Ahmed',
                'bride_name' => 'Farzana Akter',
                'location' => 'Chattogram',
                'story' => 'Tanvir and Farzana were matched at 88%. But what truly brought them together was a shared love for their families and long walks by the river. They spoke on video calls for three months, involved their parents early, and married surrounded by both their hometowns.',
                'married_on' => '2025-09-02',
                'photo_path' => 'seed-photos/story-2.svg',
                'is_published' => true,
                'is_featured' => false,
                'sort_order' => 2,
            ],
            [
                'title' => 'A Second Chance at Happiness',
                'groom_name' => 'Sabbir Rahman',
                'bride_name' => 'Sadia Islam',
                'location' => 'Sylhet',
                'story' => 'Both joining Jora after difficult marriages, Sabbir and Sadia entered with cautious hearts. Honest, unhurried conversation helped them rebuild trust. Today they are building a blended family that fills their home with laughter.',
                'married_on' => '2025-12-22',
                'photo_path' => 'seed-photos/story-3.svg',
                'is_published' => true,
                'is_featured' => false,
                'sort_order' => 3,
            ],
            [
                'title' => 'Families First, Always',
                'groom_name' => 'Imran Kabir',
                'bride_name' => 'Tasnim Chowdhury',
                'location' => 'Rajshahi',
                'story' => 'Imran\u2019s family reached out to Tasnim\u2019s family within days of their first match. From the start, both sides were involved and honest. What began as two profiles became a bond between two families who now celebrate every Eid together.',
                'married_on' => '2025-06-15',
                'photo_path' => 'seed-photos/story-4.svg',
                'is_published' => true,
                'is_featured' => false,
                'sort_order' => 4,
            ],
            [
                'title' => 'The Designers Who Found Each Other',
                'groom_name' => 'Farhan Islam',
                'bride_name' => 'Rumana Haque',
                'location' => 'Dhaka',
                'story' => 'Two designers, two portfolios, one shared sense of humour. Farhan and Rumana bonded over colour palettes and coffee, and their wedding invitations were designed by the couple themselves — a perfect beginning for two creative hearts.',
                'married_on' => '2026-01-10',
                'photo_path' => 'seed-photos/story-5.svg',
                'is_published' => false,
                'is_featured' => false,
                'sort_order' => 5,
            ],
        ];

        foreach ($stories as $story) {
            $this->seedStoryCover($story['photo_path'], $story['groom_name'], $story['bride_name']);

            SuccessStory::create($story);
        }
    }

    private function seedStoryCover(string $path, string $groom, string $bride): void
    {
        if (Storage::disk('public')->exists($path)) {
            return;
        }

        Storage::disk('public')->put($path, <<<SVG
    <svg xmlns="http://www.w3.org/2000/svg" width="900" height="600" viewBox="0 0 900 600">
      <defs>
        <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
          <stop offset="0%" stop-color="#8B1E3F"/>
          <stop offset="100%" stop-color="#D4AF37"/>
        </linearGradient>
      </defs>
      <rect width="900" height="600" fill="url(#bg)"/>
      <circle cx="760" cy="90" r="200" fill="#ffffff" fill-opacity="0.10"/>
      <circle cx="120" cy="520" r="160" fill="#ffffff" fill-opacity="0.08"/>
      <text x="450" y="170" text-anchor="middle" font-family="Inter, Arial, sans-serif" font-size="42" font-weight="700" fill="#ffffff" fill-opacity="0.95">OUR STORY</text>
      <text x="450" y="250" text-anchor="middle" font-family="Inter, Arial, sans-serif" font-size="30" font-weight="600" fill="#ffffff">Celebrating the marriage of</text>
      <text x="450" y="330" text-anchor="middle" font-family="Inter, Arial, sans-serif" font-size="56" font-weight="800" fill="#ffffff">{$groom}</text>
      <text x="450" y="395" text-anchor="middle" font-family="Inter, Arial, sans-serif" font-size="40" fill="#f6e9c8">&amp;</text>
      <text x="450" y="470" text-anchor="middle" font-family="Inter, Arial, sans-serif" font-size="56" font-weight="800" fill="#ffffff">{$bride}</text>
    </svg>
    SVG);
    }

    private function seedContacts(): void
    {
        if (Contact::query()->exists()) {
            return;
        }

        Contact::create([
            'name' => 'Mehedi Hasan',
            'email' => 'mehedi@example.com',
            'subject' => 'Question about verifying my profile',
            'message' => 'Hello, I uploaded my national ID for profile verification two days ago but the status is still pending. How long does it usually take? Thank you!',
            'created_at' => now()->subDays(3),
        ]);

        Contact::create([
            'name' => 'Sabrina Akter',
            'email' => 'sabrina@example.com',
            'subject' => 'Feature suggestion: family references',
            'message' => 'It would be really helpful to share references from the brides and grooms side before finalising a match. Just an idea!',
            'created_at' => now()->subDay(),
        ]);
    }
}