<?php

namespace App\Imports;

use App\Models\Item;
use App\Models\Category;
use App\Models\Family;
use App\Models\Group;
use App\Models\Supplier;
use App\Models\Location;
use App\Models\Warehouse;
use App\Models\BatchTracking;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ItemImport implements ToModel, WithStartRow
{
    private $itemCode = 0;
    private $successCount = 0;
    private $failureCount = 0;
    private $batchNumber = 'BATCH-00000000-000';
    private $poId;
    private $undefinedFamily;
    private $undefinedCategory;
    private $undefinedGroup;
    private $rowNumber = 1;
    private $connection;

    public function __construct()
    {
        // Get the current database connection - check session first, then default
        $sessionDb = session('active_db');
        if ($sessionDb && array_key_exists($sessionDb, config('database.connections'))) {
            $this->connection = $sessionDb;
        } else {
            $this->connection = DB::getDefaultConnection();
        }
        
        Log::info('[ItemImport] Initializing with connection', ['connection' => $this->connection]);
        
        // Ensure we're using the correct connection for all queries
        $this->poId = PurchaseOrder::on($this->connection)->first()?->id ?? null;
        
        // Create undefined fallbacks for Category, Family, and Group
        $this->undefinedCategory = Category::on($this->connection)->firstOrCreate(
            ['cat_name' => 'UNDEFINED']
        );
        
        $this->undefinedFamily = Family::on($this->connection)->firstOrCreate(
            ['family_name' => 'UNDEFINED']
        );
        
        $this->undefinedGroup = Group::on($this->connection)->firstOrCreate(
            ['group_name' => 'UNDEFINED']
        );
        
        Log::info('[ItemImport] Undefined category/family/group IDs', [
            'undefined_category_id' => $this->undefinedCategory->id,
            'undefined_family_id' => $this->undefinedFamily->id,
            'undefined_group_id' => $this->undefinedGroup->id,
        ]);
    }

    public function model(array $row)
    {
        try {
            $this->rowNumber++;
            Log::info('[ItemImport] Incoming row', ['rowNumber' => $this->rowNumber, 'row' => $row]);
            
            // NEW Excel format mapping (URS STOCK NOV 2025.xls):
            // Column A (index 0) = code1 (Stock Code)
            // Column B (index 1) = desc (Description/Item Name)
            // Column C (index 2) = category
            // Column D (index 3) = family
            // Column E (index 4) = group
            // Column F (index 5) = on hand (Stock/Quantity)
            // Column G (index 6) = cost
            // Column H (index 7) = cash
            // Column I (index 8) = term
            // Column J (index 9) = customer
            $stockCode = $this->getString($row, 0);  // Column A (index 0): code1
            $itemName = $this->getString($row, 1);  // Column B (index 1): desc
            $categoryName = $this->getString($row, 2);  // Column C (index 2): category
            $familyName = $this->getString($row, 3);  // Column D (index 3): family
            $groupName = $this->getString($row, 4);  // Column E (index 4): group
            $qty = (int) $this->getNumeric($row, 5, 0);  // Column F (index 5): on hand
            $cost = $this->getNumeric($row, 6, 0.0);  // Column G (index 6): cost
            $cashPrice = $this->getNumeric($row, 7, 0.0);  // Column H (index 7): cash
            $termPrice = $this->getNumeric($row, 8, 0.0);  // Column I (index 8): term
            $custPrice = $this->getNumeric($row, 9, 0.0);  // Column J (index 9): customer
            
            if (empty($itemName)) {
                Log::warning("Skipping row due to missing item name: " . json_encode($row));
                $this->failureCount++;
                return null;
            }
            
            $this->itemCode++;

            // Debug: Log what we're reading from Excel
            Log::info('[ItemImport] Reading item data from Excel', [
                'rowNumber' => $this->rowNumber,
                'itemName' => $itemName,
                'stockCode' => $stockCode,
                'categoryName_raw' => $categoryName,
                'familyName_raw' => $familyName,
                'groupName_raw' => $groupName,
                'qty' => $qty,
                'cost' => $cost,
                'cashPrice' => $cashPrice,
                'termPrice' => $termPrice,
                'custPrice' => $custPrice,
            ]);
            
            // Trim and normalize names for case-insensitive lookup
            $categoryName = $categoryName ? trim($categoryName) : null;
            $familyName = $familyName ? trim($familyName) : null;
            $groupName = $groupName ? trim($groupName) : null;
            
            // Skip lookup if value is "N/A" or empty
            if (strtoupper(trim($categoryName ?? '')) === 'N/A' || empty($categoryName)) {
                $categoryName = null;
            }
            if (strtoupper(trim($familyName ?? '')) === 'N/A' || empty($familyName)) {
                $familyName = null;
            }
            if (strtoupper(trim($groupName ?? '')) === 'N/A' || empty($groupName)) {
                $groupName = null;
            }
            
            // Case-insensitive lookup for category
            $category = null;
            if (!empty($categoryName)) {
                $category = Category::on($this->connection)
                    ->whereRaw('LOWER(TRIM(cat_name)) = LOWER(?)', [trim($categoryName)])
                    ->first();
                
                if (!$category) {
                    $allCategories = Category::on($this->connection)->all();
                    foreach ($allCategories as $cat) {
                        if (strtolower(trim($cat->cat_name)) === strtolower(trim($categoryName))) {
                            $category = $cat;
                            break;
                        }
                    }
                }
            }
            
            // Case-insensitive lookup for family
            $family = null;
            if (!empty($familyName)) {
                $family = Family::on($this->connection)
                    ->whereRaw('LOWER(TRIM(family_name)) = LOWER(?)', [trim($familyName)])
                    ->first();
                
                if (!$family) {
                    $allFamilies = Family::on($this->connection)->all();
                    foreach ($allFamilies as $fam) {
                        if (strtolower(trim($fam->family_name)) === strtolower(trim($familyName))) {
                            $family = $fam;
                            break;
                        }
                    }
                }
            }
            
            // Case-insensitive lookup for group
            $group = null;
            if (!empty($groupName)) {
                $group = Group::on($this->connection)
                    ->whereRaw('LOWER(TRIM(group_name)) = LOWER(?)', [trim($groupName)])
                    ->first();
                
                if (!$group) {
                    $allGroups = Group::on($this->connection)->all();
                    foreach ($allGroups as $grp) {
                        if (strtolower(trim($grp->group_name)) === strtolower(trim($groupName))) {
                            $group = $grp;
                            break;
                        }
                    }
                }
            }
            
            // Log what we found for debugging
            if (!empty($categoryName) && !$category) {
                Log::warning('[ItemImport] Category not found', [
                    'rowNumber' => $this->rowNumber,
                    'categoryName' => $categoryName,
                    'itemName' => $itemName,
                    'connection' => $this->connection,
                ]);
            }
            
            if (!empty($familyName) && !$family) {
                Log::warning('[ItemImport] Family not found', [
                    'rowNumber' => $this->rowNumber,
                    'familyName' => $familyName,
                    'itemName' => $itemName,
                    'connection' => $this->connection,
                ]);
            }
            
            if (!empty($groupName) && !$group) {
                Log::warning('[ItemImport] Group not found', [
                    'rowNumber' => $this->rowNumber,
                    'groupName' => $groupName,
                    'itemName' => $itemName,
                    'connection' => $this->connection,
                ]);
            }
            
            $category = $category ?? $this->undefinedCategory;
            $family = $family ?? $this->undefinedFamily;
            $group = $group ?? $this->undefinedGroup;
            
            // Use first supplier, warehouse, and location as defaults - use the correct connection
            $supplier = Supplier::on($this->connection)->first();
            $warehouse = Warehouse::on($this->connection)->first();
            $location = Location::on($this->connection)->first();

            Log::info('[ItemImport] Resolved relations', [
                'rowNumber' => $this->rowNumber,
                'category_id' => $category?->id,
                'family_id' => $family?->id,
                'group_id' => $group?->id,
                'supplier_id' => $supplier?->id,
                'warehouse_id' => $warehouse?->id,
                'location_id' => $location?->id,
            ]);

            // Create or update the item - use the correct connection
            $item = new Item;
            $item->setConnection($this->connection);
            $item->cat_id = $category->id;
            $item->family_id = $family->id;
            $item->group_id = $group->id;
            $item->item_name = $itemName;
            $item->item_code = $stockCode ?: ('Item Code ' . $this->itemCode);
            $item->qty = $qty;
            $item->cost = $cost;
            $item->cash_price = $cashPrice;
            $item->term_price = $termPrice;
            $item->cust_price = $custPrice;
            $item->stock_alert_level = 0;
            $item->sup_id = $supplier?->id;
            $item->warehouse_id = $warehouse?->id;
            $item->location_id = $location?->id;
            $item->um = 'UNIT'; // Default unit of measure
            $item->created_at = now();
            $item->save();

            Log::info('[ItemImport] Saved item', [
                'rowNumber' => $this->rowNumber,
                'item_id' => $item->id,
                'item_code' => $item->item_code,
                'qty' => $item->qty,
                'cost' => $item->cost,
                'cash_price' => $item->cash_price,
                'term_price' => $item->term_price,
                'cust_price' => $item->cust_price,
            ]);

            // Create batch tracking entry - use the correct connection
            BatchTracking::on($this->connection)->create([
                'batch_num' => $this->batchNumber,
                'po_id' => $this->poId,
                'item_id' => $item->id,
                'quantity' => $qty,
                'received_date' => now(),
                'received_by' => Auth::id() ?? 1, 
            ]);

            Log::info('[ItemImport] Created batch tracking', [
                'rowNumber' => $this->rowNumber,
                'item_id' => $item->id,
                'batch_num' => $this->batchNumber,
                'quantity' => $qty,
            ]);

            $this->successCount++;
            return $item;

        } catch (\Exception $e) {
            Log::error("[ItemImport] Import error", [
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
        return 4; // Start from row 4 (row 3 is the header in the new Excel format)
    }

    public function __destruct()
    {
        Log::info("[ItemImport] Import Summary", [
            'successful_imports' => $this->successCount,
            'failed_imports' => $this->failureCount,
        ]);
    }

    private function getString(array $row, int $index, ?string $default = null): ?string
    {
        if (!array_key_exists($index, $row)) {
            return $default;
        }
        $value = $row[$index];
        if ($value === null) {
            return $default;
        }
        $value = is_string($value) ? trim($value) : (is_numeric($value) ? (string)$value : '');
        // Ensure UTF-8 encoding for proper character handling (fixes degree symbols, etc.)
        if ($value !== '' && is_string($value)) {
            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }
        return $value === '' ? $default : $value;
    }

    private function getNumeric(array $row, int $index, float|int $default = 0): float|int
    {
        if (!array_key_exists($index, $row) || $row[$index] === null || $row[$index] === '') {
            return $default;
        }
        if (is_numeric($row[$index])) {
            return $row[$index] + 0; // cast to number
        }
        // Try to normalize numeric strings with commas, etc.
        $normalized = preg_replace('/[^0-9.\-]/', '', (string)$row[$index]);
        return is_numeric($normalized) ? ($normalized + 0) : $default;
    }
}