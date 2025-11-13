<?php

namespace App\Imports;

use App\Models\Supplier;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class SupplierImport implements ToModel, WithStartRow
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
                '60 DAYS' => '60 DAYS',
                'CASH' => 'CASH',
                'NET 30 DAY' => 'NET 30 DAY'
            ];
    
            $termValue = strtoupper(trim($row[12] ?? ''));
            $term = $termMappings[$termValue] ?? null;

            $supplier = new Supplier;
            $supplier->account = $row[1];
            $supplier->sup_name = $row[2];
            $supplier->address_line1 = $row[3];
            $supplier->address_line2 = $row[4];
            $supplier->address_line3 = $row[5] ?? null;
            $supplier->address_line4 = $row[6] ?? null;
            $supplier->phone_num = $row[7] ?? null;
            $supplier->fax_num = $row[8] ?? null;
            $supplier->email = $row[9] ?? null;
            $supplier->area = $row[11] ?? null;
            $supplier->term = $term;
            $supplier->business_registration_no = $row[13] ?? null;
            $supplier->gst_registration_no = $row[14] ?? null;
            // Currency comes from column O (index 14); accept both RM and MYR, default to RM
            $supplier->currency = $this->normalizeCurrency($row[14] ?? null);
            $supplier->created_at = now();
            $supplier->save();
    
            $this->successCount++;
            return $supplier;
    
        } catch (\Exception $e) {
            Log::error("Supplier Import error", [
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
        return 6; // Skip the header row
    }

    public function __destruct()
    {
        Log::info("Supplier Import Summary", [
            'successful_imports' => $this->successCount,
            'failed_imports' => $this->failureCount,
        ]);
    }
}