<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if (! $user->is_admin) {
            abort(403, 'You do not have access to the admin area.');
        }

        if ($user->status === 'suspended') {
            Auth::logout();

            return redirect()->route('login')->withErrors([
                'email' => 'This administrator account has been suspended.',
            ]);
        }

        return $next($request);
    }
}
