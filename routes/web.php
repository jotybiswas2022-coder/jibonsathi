<?php

use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Application entry routes
|--------------------------------------------------------------------------
|
| Frontend member/guest area lives in routes/frontend.php and the admin
| panel lives in routes/backend.php. Both are pulled in below so the whole
| application is served from a single entry point while still keeping the
| frontend and backend route definitions fully separated.
|
*/

// Uploaded media is streamed from the public disk so the app works
// without requiring `php artisan storage:link`.
//
// Both routes stay out of the session stack. A page such as the homepage asks
// for around forty of these, and Symfony forces any response that carries a
// session cookie to `Cache-Control: no-cache, private`, so a phone re-downloads
// every image on every visit and each one costs a full PHP boot. With the
// session middleware skipped the cached response below actually applies, and a
// repeat visit fetches nothing.
$mediaWithoutMiddleware = [
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    \App\Http\Middleware\TrackLastActivity::class,
];

Route::get('/media/avatar/{seed}.svg', [MediaController::class, 'avatar'])
    ->where('seed', '.*')
    ->name('media.avatar')
    ->withoutMiddleware($mediaWithoutMiddleware)
    ->middleware('cache.headers:public;max_age=604800');

Route::get('/media/{path}', [MediaController::class, 'show'])
    ->where('path', '.*')
    ->name('media.show')
    ->withoutMiddleware($mediaWithoutMiddleware)
    ->middleware('cache.headers:public;max_age=604800');

require __DIR__.'/frontend.php';
require __DIR__.'/backend.php';
