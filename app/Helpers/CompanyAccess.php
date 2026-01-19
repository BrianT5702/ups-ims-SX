<?php

namespace App\Helpers;

use App\Models\User;
use Spatie\Permission\Models\Role;

class CompanyAccess
{
    /**
     * Define which companies each role can access
     * 
     * @var array
     */
    private static $roleCompanyMap = [
        'Admin' => ['ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'], // Admin can access all
        'Department1' => ['ups', 'urs', 'ucs'], // Department1 can only access original companies
        'Department2' => ['ups2', 'urs2', 'ucs2'], // Department2 can only access new companies
    ];

    /**
     * Get all accessible companies for a user based on their roles
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
            // If Admin, return all companies
            if ($roleName === 'Admin') {
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
     * @param string $companyCode
     * @param User|null $user
     * @return bool
     */
    public static function canAccessCompany(string $companyCode, ?User $user = null): bool
    {
        $accessibleCompanies = self::getAccessibleCompanies($user);
        return in_array(strtolower($companyCode), $accessibleCompanies);
    }

    /**
     * Get company display name
     * 
     * @param string $companyCode
     * @return string
     */
    public static function getCompanyDisplayName(string $companyCode): string
    {
        return strtoupper($companyCode);
    }

    /**
     * Get all available companies
     * 
     * @return array
     */
    public static function getAllCompanies(): array
    {
        return ['ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];
    }
}

