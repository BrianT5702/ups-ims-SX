<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class CustomerSalesmanImport implements ToCollection
{
    private int $updatedCount = 0;
    private array $missingAccounts = [];

    public function collection(Collection $rows): void
    {
        // Row 6 (index 5) column D (index 3) contains "SALESMAN: CODE"
        $salesmanCell = (string) ($rows[5][3] ?? '');
        $salesmanCode = $this->extractSalesmanCode($salesmanCell);

        if (!$salesmanCode) {
            throw new \Exception('Could not find salesman code in row 6, column D (expected "SALESMAN: CODE").');
        }

        $customerConnection = session('active_db') ?: DB::getDefaultConnection();
        // salesman_id FK on customers references users on the same connection (UPS/URS/UCS), not the shared auth DB
        $salesman = User::on($customerConnection)
            ->where(function ($q) use ($salesmanCode) {
                $code = strtolower($salesmanCode);
                $q->whereRaw('LOWER(username) = ?', [$code])
                    ->orWhereRaw('LOWER(name) = ?', [$code]);
            })
            ->first();

        if (!$salesman) {
            throw new \Exception(
                "Salesman with code '{$salesmanCode}' not found in {$customerConnection} users. " .
                'Each company database must have a matching user (username or name) for the FK to succeed.'
            );
        }

        // Data starts at row 9 (index 8). Column B (index 1) is the account number.
        for ($i = 8; $i < $rows->count(); $i++) {
            $row = $rows[$i];

            $account = trim((string) ($row[1] ?? ''));
            if ($account === '') {
                continue; // Skip empty lines
            }

            $customer = Customer::on($customerConnection)->where('account', $account)->first();

            if (!$customer) {
                $this->missingAccounts[] = $account;
                continue;
            }

            $customer->salesman_id = $salesman->id;
            $customer->save();
            $this->updatedCount++;
        }

        if ($this->updatedCount === 0) {
            throw new \Exception('No customers were updated. Please verify the account values in column B.');
        }
    }

    private function extractSalesmanCode(string $cellValue): ?string
    {
        $normalized = strtoupper(str_replace(' ', '', $cellValue));
        $prefix = 'SALESMAN:';

        if (str_starts_with($normalized, $prefix)) {
            $code = substr($normalized, strlen($prefix));
            return $code !== '' ? $code : null;
        }

        return null;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    public function getMissingAccounts(): array
    {
        return $this->missingAccounts;
    }
}

