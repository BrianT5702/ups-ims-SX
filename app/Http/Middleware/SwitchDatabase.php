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

            // Rehydrate the authenticated user from the new connection by email
            if (auth()->check()) {
                $email = auth()->user()->email;
                $fresh = \App\Models\User::on($connection)->where('email', $email)->first();
                if ($fresh) {
                    auth()->setUser($fresh);
                }
            }
            
        }

        return $next($request);
    }
}


