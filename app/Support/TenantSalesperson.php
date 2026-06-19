<?php

namespace App\Support;

use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TenantSalesperson
{
    /**
     * Salespeople on the active tenant DB, matched to auth users with the Salesperson role by username.
     * FK columns (customers.salesman_id, delivery_orders.salesman_id, etc.) reference tenant users.id,
     * which can differ numerically from the shared UPS auth user ids (especially on UPS2/URS2/UCS2).
     */
    public static function list(?string $connection = null): Collection
    {
        $connection = $connection ?? session('active_db') ?: DB::getDefaultConnection();

        $usernames = User::query()
            ->role('Salesperson')
            ->whereNotNull('username')
            ->pluck('username');

        if ($usernames->isEmpty()) {
            return collect();
        }

        $normalized = $usernames
            ->map(fn ($username) => strtolower(trim((string) $username)))
            ->filter()
            ->unique()
            ->values();

        return CompanyUser::on($connection)
            ->where(function ($query) use ($normalized) {
                foreach ($normalized as $username) {
                    $query->orWhereRaw('LOWER(username) = ?', [$username]);
                }
            })
            ->orderBy('name')
            ->get();
    }

    public static function find(int $id, ?string $connection = null): ?CompanyUser
    {
        $connection = $connection ?? session('active_db') ?: DB::getDefaultConnection();

        return CompanyUser::on($connection)->find($id);
    }
}
