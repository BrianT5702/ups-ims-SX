<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SwitchDatabase
{
    public function handle($request, Closure $next)
    {
        $connection = Session::get('active_db');

        if ($connection && array_key_exists($connection, config('database.connections'))) {
            // Fully reset DB connections to avoid stale handles during fast switches
            foreach (array_keys(config('database.connections')) as $connName) {
                try { \DB::disconnect($connName); } catch (\Throwable $e) {}
                try { \DB::purge($connName); } catch (\Throwable $e) {}
            }

            config(['database.default' => $connection]);
            \DB::setDefaultConnection($connection);
            app()->forgetInstance('db');
            \DB::reconnect($connection);

            // Clear Spatie permission cache to avoid stale permissions across connections
            if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
                app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
            }

            // Note: Users are always stored in UPS database
            // We don't rehydrate from target database to avoid confusion
            // The authenticated user from UPS database is used for all company databases
            // Roles are checked from UPS, permissions are checked from current database
            
        }

        return $next($request);
    }
}


