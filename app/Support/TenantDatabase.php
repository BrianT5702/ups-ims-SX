<?php

namespace App\Support;

use App\Helpers\CompanyAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class TenantDatabase
{
    /**
     * Resolve which tenant connection to use (query ?db=, POST db, session, then default).
     */
    public static function resolve(?Request $request = null, ?string $explicit = null): string
    {
        $request = $request ?? request();

        $candidate = strtolower(trim((string) (
            $explicit
            ?? $request?->query('db')
            ?? $request?->input('db')
            ?? session('active_db')
            ?? DB::getDefaultConnection()
        )));

        if (!array_key_exists($candidate, config('database.connections'))) {
            $candidate = strtolower((string) (session('active_db') ?: config('database.default')));
        }

        if (!array_key_exists($candidate, config('database.connections'))) {
            throw new \RuntimeException("Invalid database connection [{$candidate}].");
        }

        return $candidate;
    }

    /**
     * Switch the app to the given tenant connection for this request.
     */
    public static function apply(string $connection): void
    {
        $connection = strtolower($connection);

        if (!array_key_exists($connection, config('database.connections'))) {
            throw new \RuntimeException("Invalid database connection [{$connection}].");
        }

        session(['active_db' => $connection]);
        config(['database.default' => $connection]);
        DB::setDefaultConnection($connection);

        try {
            DB::purge($connection);
            DB::reconnect($connection);
        } catch (\Throwable) {
            // purge/reconnect is best-effort; setDefaultConnection is what models need
        }

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    /**
     * Resolve tenant connection, verify access, and apply for the current request.
     */
    public static function resolveAndApply(?Request $request = null, ?string $explicit = null): string
    {
        $connection = self::resolve($request, $explicit);

        if (auth()->check() && !CompanyAccess::canAccessCompany($connection)) {
            throw new AccessDeniedHttpException('You do not have access to this company database.');
        }

        self::apply($connection);

        return $connection;
    }

    /**
     * Route parameters for print/preview links — always include the active tenant db.
     *
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    public static function previewRouteParams(int|string $id, array $extra = []): array
    {
        $connection = session('active_db') ?: DB::getDefaultConnection();

        return array_merge(['id' => $id, 'db' => $connection], $extra);
    }
}
