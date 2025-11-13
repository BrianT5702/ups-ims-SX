<?php

namespace App\Imports;

use App\Models\Item;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Supplier;
use App\Models\Location;
use App\Models\Warehouse;
use App\Models\BatchTracking;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ItemImport implements ToModel, WithStartRow
{
    private $itemCode = 0;
    private $successCount = 0;
    private $failureCount = 0;
    private $batchNumber = 'BATCH-00000000-000';
    private $poId;
    private $undefinedBrand;
    private $undefinedCategory;
    private $rowNumber = 1;

    public function __construct()
    {
        $this->poId = PurchaseOrder::first()?->id ?? null;
        
        $this->undefinedBrand = Brand::firstOrCreate(
            ['brand_name' => 'UNDEFINED'],
            ['description' => 'Default brand for imported items with undefined brands']
        );
        
        $this->undefinedCategory = Category::firstOrCreate(
            ['cat_name' => 'UNDEFINED'],
            ['description' => 'Default category for imported items with undefined categories']
        );
    }

    public function model(array $row)
    {
        try {
            $this->rowNumber++;
            Log::info('[ItemImport] Incoming row', ['rowNumber' => $this->rowNumber, 'row' => $row]);
            $itemName = $this->getString($row, 2);
            if (empty($itemName)) {
                Log::warning("Skipping row due to missing item name: " . json_encode($row));
                $this->failureCount++;
                return null;
            }
            
            $this->itemCode++;

            // Handle qty and price - set to default values if they are 0 or empty
            // Column order: cost - cash - term - customer
            $qty = (int) $this->getNumeric($row, 7, 0);
            $cost = (float) $this->getNumeric($row, 20, 0);
            $cashPrice = (float) $this->getNumeric($row, 20, 0);
            $termPrice = (float) $this->getNumeric($row, 20, 0);
            $custPrice = (float) $this->getNumeric($row, 20, 0);

            // Retrieve related data with fallbacks to UNDEFINED
            $categoryName = $this->getString($row, 8);
            $brandName = $this->getString($row, 9);
            $category = !empty($categoryName) ? Category::where('cat_name', $categoryName)->first() : null;
            $brand = !empty($brandName) ? Brand::where('brand_name', $brandName)->first() : null;
            
            $category = $category ?? $this->undefinedCategory;
            $brand = $brand ?? $this->undefinedBrand;
            
            // J: Supplier Name (index 9)
            $supplierName = $this->getString($row, 9);
            $supplier = !empty($supplierName) ? Supplier::where('sup_name', $supplierName)->first() : null;
            $supplier = $supplier ?? Supplier::first();
            
            // M: Warehouse Name (index 12)
            $warehouseName = $this->getString($row, 12);
            $warehouse = !empty($warehouseName) ? Warehouse::where('warehouse_name', $warehouseName)->first() : null;
            $warehouse = $warehouse ?? Warehouse::first();
            
            // N: Location Name (index 13)
            $locationName = $this->getString($row, 13);
            $location = !empty($locationName) ? Location::where('location_name', $locationName)->first() : null;
            $location = $location ?? Location::first();

            Log::info('[ItemImport] Resolved relations', [
                'rowNumber' => $this->rowNumber,
                'category_id' => $category?->id,
                'brand_id' => $brand?->id,
                'supplier_id' => $supplier?->id,
                'warehouse_id' => $warehouse?->id,
                'location_id' => $location?->id,
            ]);

            // Create or update the item
            $item = new Item;
            $item->cat_id = $category->id;
            $item->brand_id = $brand->id;
            $item->item_name = $itemName;
            $item->qty = $qty;
            $item->cost = $cost;
            $item->cash_price = $cashPrice;
            $item->term_price = $termPrice;
            $item->cust_price = $custPrice;
            // I: Stock Alert Level (index 8)
            $item->stock_alert_level = (int)$this->getNumeric($row, 8, 0);
            $item->sup_id = $supplier->id;
            $item->warehouse_id = $warehouse->id;
            $item->location_id = $location->id;
            // K: Unit of Measure (index 10)
            $item->um = $this->getString($row, 3, 'UNIT') ?? 'UNIT';
            // L: Item Code (index 11)
            $item->item_code = $this->getString($row, 1) ?: ('Item Code ' . $this->itemCode);
            // Image import removed
            $item->created_at = now();
            $item->save();

            Log::info('[ItemImport] Saved item', [
                'rowNumber' => $this->rowNumber,
                'item_id' => $item->id,
                'item_code' => $item->item_code,
                'qty' => $item->qty,
                'cost' => $item->cost,
            ]);

            // Create batch tracking entry
            BatchTracking::create([
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
        return 11;
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