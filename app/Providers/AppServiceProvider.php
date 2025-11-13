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
