<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Course;
use App\Models\UserCourseEnrollment;
use App\Models\UserLessonProgress;
use App\Observers\CacheInvalidationObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar observers para invalidação automática de cache
        User::observe(CacheInvalidationObserver::class);
        Course::observe(CacheInvalidationObserver::class);
        UserCourseEnrollment::observe(CacheInvalidationObserver::class);
        UserLessonProgress::observe(CacheInvalidationObserver::class);

        // Basic Gates for users.* policies
        Gate::define('users.view', function ($user) {
            // Any authenticated user can view users by default
            return !is_null($user);
        });

        Gate::define('users.manage', function ($user) {
            // Prefer an is_admin boolean if available; otherwise restrict
            if (Schema::hasColumn('users', 'is_admin')) {
                return (bool) ($user->is_admin ?? false);
            }
            return false;
        });
    }
}
