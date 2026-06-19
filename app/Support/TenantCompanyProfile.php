<?php

namespace App\Support;

use App\Models\CompanyProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TenantCompanyProfile
{
    /**
     * Canonical company header defaults per tenant connection.
     *
     * @return array<string, array{company_name: string, company_no: string}>
     */
    public static function companyIdentityByConnection(): array
    {
        return [
            'ups' => [
                'company_name' => 'UNITED PANEL-SYSTEM (M) SDN. BHD.',
                'company_no' => '772009-A',
            ],
            'urs' => [
                'company_name' => 'UNITED REFRIGERATION-SYSTEM (M) SDN. BHD.',
                'company_no' => '772011-D',
            ],
            'ucs' => [
                'company_name' => 'UNITED COLD-SYSTEM (M) SDN. BHD.',
                'company_no' => '748674-K',
            ],
            'ups2' => [
                'company_name' => 'UNITED PANEL-SYSTEM (M) SDN. BHD.',
                'company_no' => '772009-A',
            ],
            'urs2' => [
                'company_name' => 'UNITED REFRIGERATION-SYSTEM (M) SDN. BHD.',
                'company_no' => '772011-D',
            ],
            'ucs2' => [
                'company_name' => 'UNITED COLD-SYSTEM (M) SDN. BHD.',
                'company_no' => '748674-K',
            ],
        ];
    }

    /**
     * Full seeded profile defaults for a tenant connection.
     */
    public static function defaultsFor(string $connection): ?array
    {
        $connection = strtolower($connection);
        $identity = self::companyIdentityByConnection()[$connection] ?? null;

        if (!$identity) {
            return null;
        }

        return array_merge($identity, [
            'gst_no' => '000537624576',
            'address_line1' => 'PTD 124299, JALAN KEMPAS LAMA',
            'address_line2' => 'KAMPUNG SEELONG JAYA',
            'address_line3' => 'SKUDAI, 81300 JOHOR BAHRU, JOHOR',
            'address_line4' => '',
            'phone_num1' => '+607 5951588',
            'phone_num2' => '+607 5951288',
            'fax_num' => '+607 5951177 / 5951122',
            'email' => 'united@ur.com.my',
        ]);
    }

    /**
     * Resolve the company profile for previews/reports.
     * Prefers the row matching this tenant's company_no (avoids stale duplicate rows).
     */
    public static function resolve(?string $connection = null): ?CompanyProfile
    {
        $connection = strtolower((string) (
            $connection ?? session('active_db') ?: DB::getDefaultConnection()
        ));

        $defaults = self::defaultsFor($connection);
        $query = CompanyProfile::on($connection);

        if ($defaults) {
            $matched = (clone $query)
                ->where('company_no', $defaults['company_no'])
                ->orderBy('id')
                ->first();

            if ($matched) {
                return $matched;
            }
        }

        return $query->orderBy('id')->first();
    }

    /**
     * Ensure exactly one profile row exists per tenant and matches that company's identity.
     * Fixes Dept 2 DBs that were seeded with the old URS fallback on the first row.
     */
    public static function sync(string $connection): bool
    {
        $connection = strtolower($connection);

        if (!array_key_exists($connection, config('database.connections'))) {
            return false;
        }

        $defaults = self::defaultsFor($connection);
        if (!$defaults) {
            return false;
        }

        $table = DB::connection($connection)->table('company_profiles');
        $rows = $table->orderBy('id')->get();

        $payload = array_merge($defaults, [
            'updated_at' => now(),
        ]);

        $matched = $rows->first(fn ($row) => strcasecmp((string) $row->company_no, $defaults['company_no']) === 0);
        $keepId = null;

        if ($matched) {
            $keepId = $matched->id;
            DB::connection($connection)->table('company_profiles')
                ->where('id', $keepId)
                ->update($payload);
        } elseif ($rows->isNotEmpty()) {
            $keepId = $rows->first()->id;
            DB::connection($connection)->table('company_profiles')
                ->where('id', $keepId)
                ->update(array_merge($payload, [
                    'created_at' => $rows->first()->created_at ?? now(),
                ]));
        } else {
            DB::connection($connection)->table('company_profiles')->insert(array_merge($defaults, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            return true;
        }

        if ($keepId !== null) {
            DB::connection($connection)->table('company_profiles')
                ->where('id', '!=', $keepId)
                ->delete();
        }

        return true;
    }

    /**
     * @return list<string>
     */
    public static function tenantConnections(): array
    {
        return array_values(array_intersect(
            ['ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'],
            array_keys(config('database.connections', []))
        ));
    }

    public static function syncAll(): Collection
    {
        return collect(self::tenantConnections())->mapWithKeys(function (string $connection) {
            return [$connection => self::sync($connection)];
        });
    }
}
