<?php

use App\Http\Controllers\Frontend\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Frontend\Auth\EmailVerificationController;
use App\Http\Controllers\Frontend\Auth\NewPasswordController;
use App\Http\Controllers\Frontend\Auth\PasswordResetLinkController;
use App\Http\Controllers\Frontend\Auth\RegisteredUserController;
use App\Http\Controllers\Frontend\BlockController;
use App\Http\Controllers\Frontend\DashboardController;
use App\Http\Controllers\Frontend\DiscoverController;
use App\Http\Controllers\Frontend\FavoriteController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\InterestController;
use App\Http\Controllers\Frontend\MatchController;
use App\Http\Controllers\Frontend\MessageController;
use App\Http\Controllers\Frontend\NotificationController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\ProfileController;
use App\Http\Controllers\Frontend\ReportController;
use App\Http\Controllers\Frontend\Settings\AccountSettingsController;
use App\Http\Controllers\Frontend\Settings\CareerSettingsController;
use App\Http\Controllers\Frontend\Settings\FamilySettingsController;
use App\Http\Controllers\Frontend\Settings\LifestyleSettingsController;
use App\Http\Controllers\Frontend\Settings\NotificationSettingsController;
use App\Http\Controllers\Frontend\Settings\PasswordSettingsController;
use App\Http\Controllers\Frontend\Settings\PhotoSettingsController;
use App\Http\Controllers\Frontend\Settings\PreferenceSettingsController;
use App\Http\Controllers\Frontend\Settings\PrivacySettingsController;
use App\Http\Controllers\Frontend\Settings\ProfileSettingsController;
use App\Http\Controllers\Frontend\SuccessStoryController;
use App\Http\Controllers\Frontend\VerificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest-visible / marketing routes
|--------------------------------------------------------------------------
*/

Route::prefix('')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::get('/how-it-works', [PageController::class, 'howItWorks'])->name('pages.how-it-works');
    Route::get('/about', [PageController::class, 'about'])->name('pages.about');
    Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
    Route::post('/contact', [PageController::class, 'submitContact'])->middleware('throttle:5,1')->name('pages.contact.submit');
    Route::get('/privacy', [PageController::class, 'privacy'])->name('pages.privacy');
    Route::get('/terms', [PageController::class, 'terms'])->name('pages.terms');

    Route::get('/success-stories', [SuccessStoryController::class, 'index'])->name('success-stories.index');
    Route::get('/success-stories/{story}', [SuccessStoryController::class, 'show'])->name('success-stories.show');

    Route::get('/discover', [DiscoverController::class, 'index'])->name('discover.index');

    // A member profile is viewable by guests, but ProfilePolicy decides
    // whether the viewer is allowed to inspect it.
    Route::get('/profile/{user:username}', [ProfileController::class, 'show'])->name('profiles.show');
});

/*
|--------------------------------------------------------------------------
| Guest authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1')->name('login.store');

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:3,1')->name('register.store');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:5,1')->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->middleware('throttle:5,1')->name('password.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated member area
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active', 'visible'])->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Onboarding wizard (steps 2-6)
    Route::get('/register/step/{step}', [RegisteredUserController::class, 'showStep'])->name('register.step');
    Route::match(['post', 'put', 'patch'], '/register/step/{step}', [RegisteredUserController::class, 'storeStep'])->name('register.step.store');
    Route::post('/register/skip', [RegisteredUserController::class, 'skipToDashboard'])->name('register.skip');

    // Email verification
    Route::get('/verify-email', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:3,1')->name('verification.send');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Interactions with a profile
    Route::post('/profile/{user}/interest', [InterestController::class, 'store'])->name('interests.store');
    Route::post('/profile/{user}/favorite', [FavoriteController::class, 'store'])->name('favorites.store');
    Route::delete('/profile/{user}/favorite', [FavoriteController::class, 'destroy'])->name('favorites.destroy');
    Route::post('/profile/{user}/report', [ReportController::class, 'store'])->name('reports.store');
    Route::post('/profile/{user}/block', [BlockController::class, 'store'])->name('blocks.store');
    Route::delete('/profile/{user}/block', [BlockController::class, 'destroy'])->name('blocks.destroy');

    // Interests
    Route::get('/interests/received', [InterestController::class, 'received'])->name('interests.received');
    Route::get('/interests/sent', [InterestController::class, 'sent'])->name('interests.sent');
    Route::get('/interests/accepted', [InterestController::class, 'accepted'])->name('interests.accepted');
    Route::post('/interests/{interest}/accept', [InterestController::class, 'accept'])->name('interests.accept');
    Route::post('/interests/{interest}/reject', [InterestController::class, 'reject'])->name('interests.reject');
    Route::post('/interests/{interest}/cancel', [InterestController::class, 'cancel'])->name('interests.cancel');

    // Matches
    Route::get('/matches', [MatchController::class, 'index'])->name('matches.index');
    Route::post('/matches/refresh', [MatchController::class, 'refresh'])->name('matches.refresh');

    // Favorites
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');

    // Messages
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{conversation}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/start/{user}', [MessageController::class, 'start'])->name('messages.start');
    Route::post('/messages/{conversation}', [MessageController::class, 'store'])->middleware('throttle:30,1')->name('messages.store');
    Route::delete('/messages/{conversation}', [MessageController::class, 'destroy'])->name('messages.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/read/{notification}', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/delete/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/delete-all', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');

    // Verification centre
    Route::get('/verification', [VerificationController::class, 'index'])->name('verification.index');
    Route::post('/verification/profile', [VerificationController::class, 'submitProfile'])
        ->middleware('throttle:5,10')->name('verification.submit-profile');
    Route::post('/verification/phone', [VerificationController::class, 'sendPhone'])
        ->middleware('throttle:3,30')->name('verification.send-phone');
    Route::post('/verification/phone/confirm', [VerificationController::class, 'confirmPhone'])
        ->middleware('throttle:5,10')->name('verification.confirm-phone');

    // My reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/profile', [ProfileSettingsController::class, 'edit'])->name('profile');
        Route::match(['put', 'patch'], '/profile', [ProfileSettingsController::class, 'update'])->name('profile.update');

        Route::get('/career', [CareerSettingsController::class, 'edit'])->name('career');
        Route::match(['put', 'patch'], '/career', [CareerSettingsController::class, 'update'])->name('career.update');

        Route::get('/family', [FamilySettingsController::class, 'edit'])->name('family');
        Route::match(['put', 'patch'], '/family', [FamilySettingsController::class, 'update'])->name('family.update');

        Route::get('/lifestyle', [LifestyleSettingsController::class, 'edit'])->name('lifestyle');
        Route::match(['put', 'patch'], '/lifestyle', [LifestyleSettingsController::class, 'update'])->name('lifestyle.update');

        Route::get('/preference', [PreferenceSettingsController::class, 'edit'])->name('preference');
        Route::match(['put', 'patch'], '/preference', [PreferenceSettingsController::class, 'update'])->name('preference.update');

        Route::get('/photos', [PhotoSettingsController::class, 'index'])->name('photos');
        Route::post('/photos', [PhotoSettingsController::class, 'store'])->name('photos.store');
        Route::put('/photos/{photo}/primary', [PhotoSettingsController::class, 'primary'])->name('photos.primary');
        Route::delete('/photos/{photo}', [PhotoSettingsController::class, 'destroy'])->name('photos.destroy');

        Route::get('/privacy', [PrivacySettingsController::class, 'edit'])->name('privacy');
        Route::match(['put', 'patch'], '/privacy', [PrivacySettingsController::class, 'update'])->name('privacy.update');

        Route::get('/notifications', [NotificationSettingsController::class, 'edit'])->name('notifications');
        Route::match(['put', 'patch'], '/notifications', [NotificationSettingsController::class, 'update'])->name('notifications.update');

        Route::get('/password', [PasswordSettingsController::class, 'edit'])->name('password');
        Route::put('/password', [PasswordSettingsController::class, 'update'])->name('password.update');

        Route::get('/account', [AccountSettingsController::class, 'edit'])->name('account');
        Route::post('/account/deactivate', [AccountSettingsController::class, 'deactivate'])->name('account.deactivate');
        Route::post('/account/reactivate', [AccountSettingsController::class, 'reactivate'])->name('account.reactivate');
        Route::delete('/account', [AccountSettingsController::class, 'destroy'])->name('account.destroy');

        Route::get('/blocked', [BlockController::class, 'index'])->name('blocked');
    });
});