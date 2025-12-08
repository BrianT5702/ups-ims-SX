<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
            // Required fields: Account (A), Name (B), Address 1 (C)
            if (empty($row[0]) || empty($row[1]) || empty($row[2])) {
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
            // Column mapping: A=Account, B=Name, C=Address1, D=Address2, E=Address3, F=Address4,
            // G=Contact Person, H=Phone, I=Fax, J=Email, K=Class, L=Area, M=Term,
            // N=Business Reg No, O=GST Reg No
            $customer = new Customer;
            $customer->account = $row[0];  // Column A
            $customer->cust_name = $row[1];  // Column B
            $customer->address_line1 = $row[2];  // Column C
            $customer->address_line2 = $row[3] ?? null;  // Column D
            $customer->address_line3 = $row[4] ?? null;  // Column E
            $customer->address_line4 = $row[5] ?? null;  // Column F
            // Column G: Contact person name (not stored - field doesn't exist in database)
            $customer->phone_num = $row[7] ?? null;  // Column H
            $customer->fax_num = $row[8] ?? null;  // Column I
            $customer->email = $row[9] ?? null;  // Column J
            // Column K: Class mapped to pricing_tier
            $customer->pricing_tier = $row[10] ?? null;  // Column K
            $customer->area = $row[11] ?? null;  // Column L
            $customer->term = $term;  // Column M
            $customer->business_registration_no = $row[13] ?? null;  // Column N
            $customer->gst_registration_no = $row[14] ?? null;  // Column O
            // Currency comes from column P (index 15); accept both RM and MYR, default to RM
            $customer->currency = $this->normalizeCurrency($row[15] ?? null);
            // Default salesman: first Salesperson role user from current database
            $connection = session('active_db') ?: DB::getDefaultConnection();
            $defaultSalesman = User::on($connection)->role('Salesperson')->orderBy('id')->first();
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