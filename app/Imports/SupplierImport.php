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
            // Required fields: Account (B), Name (C), Address (D)
            // Note: Excel columns start from B, so indices are offset (B=1, C=2, D=3, etc.)
            if (empty($row[1]) || empty($row[2]) || empty($row[3])) {
                Log::warning("Skipping row due to missing required fields: " . json_encode($row));
                $this->failureCount++;
                return null;
            }
    
            // Column mapping: B=Account, C=Name, D=Address, F=Business Reg No, G=GST Reg No, I=Tel & Fax
            $supplier = new Supplier;
            $supplier->account = $row[1];  // Column B
            $supplier->sup_name = $row[2];  // Column C
            $supplier->address_line1 = $row[3];  // Column D
            $supplier->address_line2 = null;
            $supplier->address_line3 = null;
            $supplier->address_line4 = null;
            $supplier->business_registration_no = $row[5] ?? null;  // Column F
            $supplier->gst_registration_no = $row[6] ?? null;  // Column G
            
            // Column J: Tel & Fax - try to parse if it contains both
            $telFax = trim($row[8] ?? '');
            if (!empty($telFax)) {
                // Try to split by common separators
                if (strpos($telFax, '/') !== false) {
                    $parts = explode('/', $telFax, 2);
                    $supplier->phone_num = trim($parts[0]) ?: null;
                    $supplier->fax_num = trim($parts[1]) ?: null;
                } elseif (strpos($telFax, '|') !== false) {
                    $parts = explode('|', $telFax, 2);
                    $supplier->phone_num = trim($parts[0]) ?: null;
                    $supplier->fax_num = trim($parts[1]) ?: null;
                } elseif (strpos($telFax, ',') !== false) {
                    $parts = explode(',', $telFax, 2);
                    $supplier->phone_num = trim($parts[0]) ?: null;
                    $supplier->fax_num = trim($parts[1]) ?: null;
                }else {
                    // If no separator, assume it's phone number
                    $supplier->phone_num = $telFax;
                    $supplier->fax_num = null;
                }
            } else {
                $supplier->phone_num = null;
                $supplier->fax_num = null;
            }
            
            $supplier->email = null;
            $supplier->area = null;
            $supplier->term = null;
            // Default currency to RM
            $supplier->currency = 'RM';
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
        return 9; // Start from row 9
    }

    public function __destruct()
    {
        Log::info("Supplier Import Summary", [
            'successful_imports' => $this->successCount,
            'failed_imports' => $this->failureCount,
        ]);
    }
}