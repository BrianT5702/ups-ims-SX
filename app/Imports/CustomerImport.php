<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class CustomerImport implements ToModel, WithStartRow
{
    private $successCount = 0;
    private $failureCount = 0;
    
    private function normalizeCurrency($value): string
    {
        $v = strtoupper(trim((string)($value ?? '')));
        if ($v === '' || $v === 'MYR' || $v === 'RM') {
            return 'RM';
        }
        return $v;
    }

    public function model(array $row)
    {
        try {
            if (empty($row[1]) || empty($row[2]) || empty($row[3])) {
                Log::warning("Skipping row due to missing required fields: " . json_encode($row));
                $this->failureCount++;
                return null;
            }
    
            $termMappings = [
                'COD' => 'C.O.D',
                'C.O.D' => 'C.O.D',
                '30 DAYS' => '30 DAYS',
                'CASH' => 'CASH'
            ];
    
            $termValue = strtoupper(trim($row[12] ?? ''));
            $term = $termMappings[$termValue] ?? null;
    
            // Create or update the customer
            $customer = new Customer;
            $customer->account = $row[1];
            $customer->cust_name = $row[2];
            $customer->address_line1 = $row[3];
            $customer->address_line2 = $row[4] ?? null;
            $customer->address_line3 = $row[5] ?? null;
            $customer->address_line4 = $row[6] ?? null;
            $customer->phone_num = $row[7] ?? null;
            $customer->fax_num = $row[8] ?? null;
            $customer->email = $row[9] ?? null;
            $customer->area = $row[11] ?? null;
            $customer->term = $term;
            $customer->business_registration_no = $row[13] ?? null;
            $customer->gst_registration_no = $row[14] ?? null;
            // Currency comes from column P (index 15); accept both RM and MYR, default to RM
            $customer->currency = $this->normalizeCurrency($row[15] ?? null);
            // Pricing tier per item in DO; remove customer-level tier
            // Default salesman: first Salesperson role user
            $defaultSalesman = User::role('Salesperson')->orderBy('id')->first();
            $customer->salesman_id = $defaultSalesman?->id;
            $customer->created_at = now();
            $customer->save();
    
            $this->successCount++;
            return $customer;
    
        } catch (\Exception $e) {
            Log::error("Customer Import error", [
                'message' => $e->getMessage(),
                'row' => $row,
                'trace' => $e->getTraceAsString()
            ]);
            $this->failureCount++;
            return null;
        }
    }

    public function startRow(): int
    {
        return 6;
    }

    public function __destruct()
    {
        Log::info("Customer Import Summary", [
            'successful_imports' => $this->successCount,
            'failed_imports' => $this->failureCount,
        ]);
    }
}