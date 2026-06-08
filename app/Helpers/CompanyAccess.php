<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\Role;

class CompanyAccess
{
    /**
     * Map roles to their accessible companies
     * Super Admin and Admin have access to all companies (handled separately)
     */
    private static $roleCompanyMap = [
        'Super Admin' => ['ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'], // All companies
        'Admin' => ['ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'], // All companies
        'Department 1' => ['ups', 'urs', 'ucs'],
        'Department1' => ['ups', 'urs', 'ucs'],
        'Department 2' => ['ups2', 'urs2', 'ucs2'],
        'Department2' => ['ups2', 'urs2', 'ucs2'],
        'Department 2 Admin' => ['ups2', 'urs2', 'ucs2'],
        'Department2 Admin' => ['ups2', 'urs2', 'ucs2'],
        // Add other role mappings here if needed
        // 'Salesperson' => ['ups'],
        // 'User' => ['ups'],
    ];

    /**
     * All tenant database connection names (Department 1 + Department 2).
     */
    public static function allCompanyConnections(): array
    {
        return ['ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];
    }

    /**
     * Get all companies accessible by the given user
     *
     * @param User|null $user
     * @return array
     */
    public static function getAccessibleCompanies(?User $user = null): array
    {
        if (!$user) {
            $user = auth()->user();
        }

        if (!$user) {
            return [];
        }

        $accessibleCompanies = [];

        // Users are stored in UPS database, so we must explicitly query roles from UPS
        // This ensures we get roles even when the current database connection has changed
        // Try to get roles directly from user relationship first (faster)
        try {
            $userRoles = $user->roles->pluck('name')->toArray();
            // If empty, query directly from UPS database
            if (empty($userRoles)) {
                $userRoles = Role::on('ups')
                    ->whereHas('users', function($query) use ($user) {
                        $query->where('users.id', $user->id);
                    })
                    ->pluck('name')
                    ->toArray();
            }
        } catch (\Exception $e) {
            // Fallback: query directly from UPS database
            $userRoles = Role::on('ups')
                ->whereHas('users', function($query) use ($user) {
                    $query->where('users.id', $user->id);
                })
                ->pluck('name')
                ->toArray();
        }

        // Check each role the user has
        foreach ($userRoles as $roleName) {
            // If Super Admin or Admin, return all companies immediately
            if ($roleName === 'Super Admin' || $roleName === 'Admin') {
                return ['ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];
            }

            // Add companies for this role
            if (isset(self::$roleCompanyMap[$roleName])) {
                $accessibleCompanies = array_merge(
                    $accessibleCompanies,
                    self::$roleCompanyMap[$roleName]
                );
            }
        }

        // Remove duplicates and return
        return array_unique($accessibleCompanies);
    }

    /**
     * Check if a user can access a specific company
     *
     * @param string $connection
     * @param User|null $user
     * @return bool
     */
    public static function canAccessCompany(string $connection, ?User $user = null): bool
    {
        $accessibleCompanies = self::getAccessibleCompanies($user);
        return in_array(strtolower($connection), array_map('strtolower', $accessibleCompanies));
    }

    /**
     * Login default: users who only have Dept 2 companies should land on UPS2, not UPS.
     */
    public static function landsOnDepartment2ByDefault(?User $user = null): bool
    {
        $accessible = array_values(self::getAccessibleCompanies($user));
        if ($accessible === []) {
            return false;
        }

        foreach (['ups', 'urs', 'ucs'] as $dept1) {
            if (in_array($dept1, $accessible, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * DO form/list: show Invoice No for UPS, UCS, and all Department 2 companies.
     */
    public static function showsDoInvoiceNo(?string $connection = null): bool
    {
        $connection = strtolower((string) ($connection ?? session('active_db') ?? ''));

        return in_array($connection, ['ups', 'ucs'], true)
            || self::isDepartment2Connection($connection);
    }

    /**
     * Department 2 tenant databases (UPS2, URS2, UCS2).
     */
    public static function isDepartment2Connection(?string $connection = null): bool
    {
        $connection = strtolower((string) ($connection ?? session('active_db') ?? ''));

        return in_array($connection, ['ups2', 'urs2', 'ucs2'], true);
    }

    /**
     * Inventory qty for display: Dept 2 never shows negatives (floor at 0).
     */
    public static function displayInventoryQty(float|int|string|null $qty, ?string $connection = null): float
    {
        $qty = (float) ($qty ?? 0);

        if (self::isDepartment2Connection($connection)) {
            return max(0.0, $qty);
        }

        return $qty;
    }
}
