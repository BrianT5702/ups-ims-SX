<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;


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
        Gate::before(function ($user, $ability) {
            try {
                // Super Admin bypass: any user with the Super Admin role can do everything
                if (method_exists($user, 'hasRole') && $user->hasRole('Super Admin')) {
                    return true;
                }

                // Optional: support explicit per-user denies
                $denied = $user->denied_permissions ?? [];
                if (is_array($denied) && in_array($ability, $denied, true)) {
                    return false;
                }
            } catch (\Throwable $e) {
                Log::warning('Gate before denied check failed: ' . $e->getMessage());
            }
        });
    }
}
