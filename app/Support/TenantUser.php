<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
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

        $localUser = User::on($connection)
            ->where(function ($q) use ($authUser) {
                if ($authUser->username) {
                    $q->whereRaw('LOWER(username) = ?', [strtolower($authUser->username)]);
                }
                if ($authUser->email) {
                    $q->orWhereRaw('LOWER(email) = ?', [strtolower($authUser->email)]);
                }
            })
            ->first();

        if (!$localUser) {
            throw new RuntimeException(
                "Your account ({$authUser->username}) is not set up in the {$connection} database. "
                . 'Please contact an administrator to add your user to this company database.'
            );
        }

        return (int) $localUser->id;
    }
}
