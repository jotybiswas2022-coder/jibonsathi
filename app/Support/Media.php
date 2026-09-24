<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class Media
{
    /**
     * Resolve a stored path into a browsable URL.
     *
     * Uploaded files live on the public disk but are streamed through the
     * `/media/{path}` route so the app works without `storage:link`.
     */
    public static function url(?string $path, ?string $fallbackName = null): string
    {
        if (blank($path)) {
            return self::avatar($fallbackName ?? 'Jora');
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return '/media/'.ltrim($path, '/');
    }

    /**
     * Deterministic gradient avatar built from initials — no network required.
     */
    public static function avatar(string $seed): string
    {
        return '/media/avatar/'.rawurlencode(mb_substr(trim($seed) ?: 'Jora', 0, 40)).'.svg';
    }

    public static function exists(?string $path): bool
    {
        return filled($path) && Storage::disk('public')->exists($path);
    }
}
