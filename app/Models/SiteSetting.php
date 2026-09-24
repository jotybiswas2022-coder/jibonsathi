<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
    ];

    /**
     * Sensible defaults so the site renders before the seeder runs.
     *
     * @var array<string, string>
     */
    public const DEFAULTS = [
        'site_name' => 'Jora',
        'tagline' => 'Bringing Two Lives Together.',
        'contact_email' => 'hello@jora.example',
        'contact_phone' => '+880 1700 000000',
        'address' => 'Gulshan Avenue, Dhaka, Bangladesh',
        'facebook_url' => 'https://facebook.com',
        'instagram_url' => 'https://instagram.com',
        'twitter_url' => 'https://twitter.com',
        'linkedin_url' => 'https://linkedin.com',
        'seo_title' => 'Jora — Free Matrimony for Meaningful Marriages',
        'seo_description' => 'Jora is a completely free matrimony platform connecting serious, verified profiles who are ready to build a life together.',
        'hero_headline' => 'Find Someone Who Complements Your Life',
        'hero_subheading' => 'Meaningful connections, genuine profiles, and a better way to find your life partner.',
        'footer_about' => 'Jora is a free, privacy-first matrimony platform built for people who are serious about finding a life partner.',
        'privacy_policy' => '',
        'terms_conditions' => '',
    ];

    public static function get(string $key, ?string $default = null): string
    {
        $settings = static::all_cached();

        return $settings[$key] ?? $default ?? self::DEFAULTS[$key] ?? '';
    }

    public static function put(string $key, ?string $value, string $group = 'general'): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        Cache::forget('site_settings');
    }

    /**
     * @return array<string, string>
     */
    public static function all_cached(): array
    {
        return Cache::remember('site_settings', now()->addMinutes(30), function (): array {
            try {
                return static::query()->pluck('value', 'key')->map(fn ($v) => (string) $v)->all();
            } catch (\Throwable) {
                return [];
            }
        });
    }

    public static function flush(): void
    {
        Cache::forget('site_settings');
    }
}
