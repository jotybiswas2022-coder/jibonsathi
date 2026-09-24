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
Route::get('/media/avatar/{seed}.svg', [MediaController::class, 'avatar'])
    ->where('seed', '.*')
    ->name('media.avatar')
    ->middleware('cache.headers:public;max_age=604800');

Route::get('/media/{path}', [MediaController::class, 'show'])
    ->where('path', '.*')
    ->name('media.show');

require __DIR__.'/frontend.php';
require __DIR__.'/backend.php';
