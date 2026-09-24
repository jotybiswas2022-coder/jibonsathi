<?php

namespace App\Providers;

use App\Models\Conversation;
use App\Models\Interest;
use App\Models\Profile;
use App\Models\Report;
use App\Models\SuccessStory;
use App\Models\User;
use App\Policies\ConversationPolicy;
use App\Policies\InterestPolicy;
use App\Policies\ProfilePolicy;
use App\Policies\ReportPolicy;
use App\Policies\SuccessStoryPolicy;
use App\Policies\UserPolicy;
use App\Support\Reference;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerPolicies();
        $this->registerBladeComponents();
        $this->registerBladeDirectives();
        $this->registerListeners();

        Paginator::defaultView('frontend.components.pagination');
        Paginator::defaultSimpleView('frontend.components.pagination-simple');

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }

    private function registerPolicies(): void
    {
        // UserPolicy owns member-to-member abilities and admin management;
        // ProfilePolicy owns moderation of profile records.
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Profile::class, ProfilePolicy::class);
        Gate::policy(Interest::class, InterestPolicy::class);
        Gate::policy(Conversation::class, ConversationPolicy::class);
        Gate::policy(Report::class, ReportPolicy::class);
        Gate::policy(SuccessStory::class, SuccessStoryPolicy::class);

        // Admins implicitly pass every ability in the panel.
        Gate::before(function (User $user, string $ability) {
            return $user->is_admin ? true : null;
        });
    }

    /**
     * Frontend and backend component namespaces keep the two interfaces apart.
     * Usage: <x-frontend::profile-card /> and <x-backend::stat-card />
     */
    private function registerBladeComponents(): void
    {
        Blade::anonymousComponentNamespace('frontend.components', 'frontend');
        Blade::anonymousComponentNamespace('backend.components', 'backend');
    }

    private function registerBladeDirectives(): void
    {
        Blade::directive('active', function (string $expression) {
            return "<?php echo request()->routeIs({$expression}) ? 'is-active' : ''; ?>";
        });

        Blade::if('admin', fn () => auth()->check() && auth()->user()->is_admin);
    }

    private function registerListeners(): void
    {
        Event::listen(Registered::class, SendEmailVerificationNotification::class);
    }
}
