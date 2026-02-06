<?php

namespace App\Helpers;

use App\Models\User;
use Spatie\Permission\Models\Role;

class CompanyAccess
{
    /**
     * Map roles to their accessible companies
     * Super Admin and Admin have access to all companies (handled separately)
     */
    private static $roleCompanyMap = [
        'Super Admin' => ['ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'], // All companies
        'Admin' => ['ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'], // All companies
        // Add other role mappings here if needed
        // 'Salesperson' => ['ups'],
        // 'User' => ['ups'],
    ];

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
}
