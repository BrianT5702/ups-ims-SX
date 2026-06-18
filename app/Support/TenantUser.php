<?php

namespace App\Support;

use App\Helpers\CompanyAccess;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class TenantUser
{
    /**
     * Map the authenticated (UPS) user to the matching user id on the active tenant database.
     * FK columns on tenant tables reference users on that connection, not the shared auth store.
     */
    public static function resolveId(?User $authUser = null, ?string $connection = null): int
    {
        $authUser = $authUser ?? auth()->user();
        if (!$authUser) {
            throw new RuntimeException('No authenticated user.');
        }

        $connection = $connection ?? session('active_db') ?: DB::getDefaultConnection();

        $localUser = self::findLocalUser($authUser, $connection);

        if (!$localUser) {
            if (!CompanyAccess::canAccessCompany($connection, $authUser)) {
                throw new RuntimeException(
                    "Your account ({$authUser->username}) is not set up in the {$connection} database. "
                    . 'Please contact an administrator to add your user to this company database.'
                );
            }

            $localUser = self::provisionLocalUser($authUser, $connection);
        }

        return (int) $localUser->id;
    }

    private static function findLocalUser(User $authUser, string $connection): ?User
    {
        return User::on($connection)
            ->where(function ($q) use ($authUser) {
                if ($authUser->username) {
                    $q->whereRaw('LOWER(username) = ?', [strtolower($authUser->username)]);
                }
                if ($authUser->email) {
                    $q->orWhereRaw('LOWER(email) = ?', [strtolower($authUser->email)]);
                }
            })
            ->first();
    }

    /**
     * Create a shadow user row on the tenant DB so FK constraints succeed.
     * Login always goes through the shared UPS auth store.
     */
    private static function provisionLocalUser(User $authUser, string $connection): User
    {
        $matchKey = $authUser->username
            ? ['username' => $authUser->username]
            : ['email' => $authUser->email];

        return User::on($connection)->updateOrCreate($matchKey, [
            'name' => $authUser->name,
            'email' => $authUser->email,
            'phone_num' => $authUser->phone_num ?? '',
            'password' => Str::random(32),
        ]);
    }
}
