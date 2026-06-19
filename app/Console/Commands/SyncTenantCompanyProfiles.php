<?php

namespace App\Console\Commands;

use App\Support\TenantCompanyProfile;
use Illuminate\Console\Command;

class SyncTenantCompanyProfiles extends Command
{
    protected $signature = 'tenant:sync-company-profiles
                            {--connection= : Sync a single tenant connection (e.g. ups2)}
                            {--all : Sync all tenant connections (default)}';

    protected $description = 'Ensure each tenant database has the correct company profile header (fixes Dept 2 URS fallback rows).';

    public function handle(): int
    {
        $connection = $this->option('connection');

        if ($connection) {
            $connection = strtolower($connection);
            if (!TenantCompanyProfile::sync($connection)) {
                $this->error("Could not sync company profile for [{$connection}].");

                return self::FAILURE;
            }

            $profile = TenantCompanyProfile::resolve($connection);
            $this->info("[{$connection}] {$profile?->company_name}");

            return self::SUCCESS;
        }

        $results = TenantCompanyProfile::syncAll();

        foreach ($results as $conn => $ok) {
            $profile = TenantCompanyProfile::resolve($conn);
            $status = $ok ? 'OK' : 'FAILED';
            $name = $profile?->company_name ?? '(none)';
            $this->line("[{$conn}] {$status} — {$name}");
        }

        return self::SUCCESS;
    }
}
