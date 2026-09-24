<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsVisible
{
    /**
     * Members whose profile has been rejected keep access to their settings so
     * they can fix the problems, but every other member area page is blocked.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->profile?->profile_status === 'rejected' && ! $request->routeIs('settings.*', 'verification.*', 'notifications.*', 'logout')) {
            return redirect()
                ->route('settings.profile')
                ->with('warning', 'Your profile needs changes before you can browse matches. Please review the notes and update your details.');
        }

        return $next($request);
    }
}
