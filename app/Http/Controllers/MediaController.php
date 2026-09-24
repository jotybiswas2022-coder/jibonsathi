<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    /**
     * Stream an uploaded file from the public disk (no storage:link needed).
     */
    public function show(string $path): StreamedResponse
    {
        abort_unless(Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path);
    }

    /**
     * Deterministic gradient initials avatar rendered as SVG.
     */
    public function avatar(string $seed): Response
    {
        $seed = rawurldecode($seed);
        $seed = preg_replace('/\.svg$/', '', $seed) ?: 'Jora';
        $seed = mb_substr(trim($seed), 0, 40);

        $hash = md5($seed);
        $hue = hexdec(substr($hash, 0, 4)) % 360;
        $hue2 = ($hue + 42) % 360;

        $words = preg_split('/\s+/', trim($seed)) ?: [];
        $initials = strtoupper(mb_substr($words[0] ?? 'J', 0, 1));
        if (count($words) > 1) {
            $initials .= strtoupper(mb_substr($words[count($words) - 1], 0, 1));
        } elseif (mb_strlen($words[0] ?? '') > 1) {
            $initials = strtoupper(mb_substr($words[0], 0, 2));
        }

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="256" height="256" viewBox="0 0 256 256">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="hsl({$hue},58%,42%)"/>
      <stop offset="100%" stop-color="hsl({$hue2},62%,58%)"/>
    </linearGradient>
  </defs>
  <rect width="256" height="256" fill="url(#g)"/>
  <circle cx="205" cy="52" r="70" fill="#ffffff" fill-opacity="0.10"/>
  <circle cx="46" cy="215" r="52" fill="#ffffff" fill-opacity="0.08"/>
  <text x="50%" y="50%" dy="0.35em" text-anchor="middle"
        font-family="Inter, 'Segoe UI', Arial, sans-serif"
        font-size="104" font-weight="600" fill="#ffffff" fill-opacity="0.96">{$initials}</text>
</svg>
SVG;

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }
}
