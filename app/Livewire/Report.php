<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Item;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ItemsExport;
use Illuminate\Support\Facades\Log;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Response;

class Report extends Component
{
    public $fileType = 'pdf';
    public $selectedColumns = [];
    public $isGenerating = false;
    public $errorMessage = '';
    public $stockFilter = 'all'; // all | gt0 | eq0
    
    public $availableColumns = [
        'item_code' => 'Item Code',
        'item_name' => 'Item Name',
        'category_name' => 'Category Name',
        'brand_name' => 'Brand Name',
        'warehouse_name' => 'Warehouse Name',
        'location_name' => 'Location Name',
        'qty' => 'Quantity', 
        'cost' => 'Cost',
        'cash_price' => 'Cash Price',
        'term_price' => 'Term Price',
        'cust_price' => 'Customer Price',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At'
    ];

    protected $rules = [
        'fileType' => 'required|in:pdf,excel',
        'selectedColumns' => 'required|array|min:1',
        'stockFilter' => 'required|in:all,gt0,eq0'
    ];

    public function mount()
    {
        $this->selectedColumns = ['item_code', 'item_name', 'qty', 'cost', 'cash_price', 'term_price', 'cust_price'];
        $this->stockFilter = 'all';
    }

    public function generateReport()
    {
        try {
            $this->validate();
            $this->isGenerating = true;
            $this->errorMessage = '';
    
            $finalColumns = array_unique(array_merge(
                ['item_code', 'item_name'],
                $this->selectedColumns
            ));
    
            // Define table joins correctly
            $query = Item::select([
                'items.item_code',
                'items.item_name',
                'items.qty',
                'items.cost',
                'items.cust_price',
                'items.term_price',
                'items.cash_price',
                'items.created_at',
                'items.updated_at',
            ]);
    
            // Add additional fields if selected
            if (in_array('brand_name', $finalColumns)) {
                $query->addSelect('brands.brand_name');
            }
            if (in_array('category_name', $finalColumns)) {
                $query->addSelect('categories.cat_name as category_name');
            }
            if (in_array('warehouse_name', $finalColumns)) {
                $query->addSelect('warehouses.warehouse_name');
            }
            if (in_array('location_name', $finalColumns)) {
                $query->addSelect('locations.location_name');
            }
    
            // Join tables correctly
            $query->leftJoin('brands', 'items.brand_id', '=', 'brands.id')
                  ->leftJoin('categories', 'items.cat_id', '=', 'categories.id')
                  ->leftJoin('warehouses', 'items.warehouse_id', '=', 'warehouses.id')
                  ->leftJoin('locations', 'items.location_id', '=', 'locations.id');
    
            // Apply stock filter
            switch ($this->stockFilter) {
                case 'gt0':
                    $query->where('items.qty', '>', 0);
                    break;
                case 'eq0':
                    $query->where('items.qty', '=', 0);
                    break;
                case 'all':
                default:
                    // no filter
                    break;
            }

            $items = $query->get();

            if ($items->isEmpty()) {
                switch ($this->stockFilter) {
                    case 'gt0':
                        $this->errorMessage = 'No items found with quantity > 0.';
                        break;
                    case 'eq0':
                        $this->errorMessage = 'No items found with quantity = 0.';
                        break;
                    case 'all':
                    default:
                        $this->errorMessage = 'No items available to generate report.';
                        break;
                }
                $this->isGenerating = false;
                return null;
            }
    
            $response = $this->fileType === 'pdf'
                ? $this->downloadPDF($items)
                : $this->downloadExcel($items);
    
            $this->isGenerating = false;
            return $response;
    
        } catch (\Exception $e) {
            Log::error('Report generation failed: ' . $e->getMessage());
            $this->errorMessage = 'Failed to generate report. Please try again.';
            $this->isGenerating = false;
            return null;
        }
    }
    

    protected function downloadPDF($items)
    {
        try {
            $pdf = PDF::loadView('reports.items', [
                'items' => $items,
                'columns' => array_intersect_key($this->availableColumns, array_flip($this->selectedColumns))
            ]);

            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, 'inventory_report_' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            throw new \Exception('PDF generation failed: ' . $e->getMessage());
        }
    }

    protected function downloadExcel($items)
    {
        try {
            return Excel::download(
                new ItemsExport($items, $this->selectedColumns), 
                'inventory_report_' . date('Y-m-d') . '.xlsx'
            );
        } catch (\Exception $e) {
            throw new \Exception('Excel generation failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.report')->layout('layouts.app');
    }
}