<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class StealthModeScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        // Check if stealth mode is active
        if ($this->isStealthModeActive()) {
            // Check if current user is Super Admin
            if (!auth()->check() || !auth()->user()->hasRole('Super Admin')) {
                // Hide all transactions for non-super-admin users
                $builder->whereRaw('1 = 0'); // This will return no results
            }
            // Super Admin can see all transactions, so no filter applied
        }
        // If stealth mode is inactive, show all transactions normally
    }

    /**
     * Check if stealth mode is currently active
     *
     * @return bool
     */
    protected function isStealthModeActive(): bool
    {
        // Use cache to avoid repeated database queries
        return Cache::remember('stealth_mode_active', 60, function () {
            try {
                $setting = DB::connection('ups')->table('stealth_settings')
                    ->where('key', 'transaction_stealth_mode')
                    ->value('value');
                
                return $setting === '1' || $setting === 1 || $setting === true;
            } catch (\Exception $e) {
                // If table doesn't exist or connection fails, assume inactive
                return false;
            }
        });
    }
}

