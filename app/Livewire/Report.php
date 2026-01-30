<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Item;
use App\Models\CompanyProfile;
use App\Models\Family;
use App\Models\Category;
use App\Models\Group;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ItemsExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class Report extends Component
{
    public $fileType = 'pdf';
    public $selectedColumns = [];
    public $isGenerating = false;
    public $errorMessage = '';
    public $stockFilter = 'all'; // all | gt0 | eq0
    public $selectedGroupId = null; // Filter by Group
    public $selectedFamilyId = null; // Filter by Family (Brand)
    public $selectedCategoryId = null; // Filter by Category (Type)
    public $sortByType = false;
    public $sortByBrand = false;
    public $sortByGroup = false;
    public $showGrouping = true; // Show GROUP/BRAND/TYPE headers
    
    public $availableColumns = [
        'item_code' => 'Stock Code',
        'item_name' => 'Stock Description',
        'qty' => 'Quantity', 
        'cost' => 'Cost Price',
        'cash_price' => 'Cash Price',
        'term_price' => 'Term Price',
        'cust_price' => 'Customer',
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
        $this->selectedGroupId = null;
        $this->selectedFamilyId = null;
        $this->selectedCategoryId = null;
        $this->sortByType = false;
        $this->sortByBrand = false;
        $this->sortByGroup = false;
        $this->showGrouping = true;
    }

    public function generateReport()
    {
        $this->errorMessage = '';
        $this->isGenerating = true;
        
        try {
            $this->validate();
    
            $finalColumns = array_unique(array_merge(
                ['item_code', 'item_name'],
                $this->selectedColumns
            ));
    
            // Define table joins correctly - only select fields that are needed
            $query = Item::select([
                'items.item_code',
                'items.item_name',
            ]);
    
            // Add additional fields if selected
            if (in_array('qty', $finalColumns)) {
                $query->addSelect('items.qty');
            }
            if (in_array('cost', $finalColumns)) {
                $query->addSelect('items.cost');
            }
            if (in_array('cust_price', $finalColumns)) {
                $query->addSelect('items.cust_price');
            }
            if (in_array('term_price', $finalColumns)) {
                $query->addSelect('items.term_price');
            }
            if (in_array('cash_price', $finalColumns)) {
                $query->addSelect('items.cash_price');
            }
    
            // Join tables correctly (always join for sorting and grouping)
            $query->leftJoin('categories', 'items.cat_id', '=', 'categories.id')
                  ->leftJoin('families', 'items.family_id', '=', 'families.id')
                  ->leftJoin('groups', 'items.group_id', '=', 'groups.id')
                  ->leftJoin('suppliers', 'items.sup_id', '=', 'suppliers.id')
                  ->addSelect('groups.group_name', 'families.family_name', 'categories.cat_name');
    
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

            // Apply Group filter
            if ($this->selectedGroupId) {
                $query->where('items.group_id', '=', $this->selectedGroupId);
            }

            // Apply Family (Brand) filter
            if ($this->selectedFamilyId) {
                $query->where('items.family_id', '=', $this->selectedFamilyId);
            }

            // Apply Category (Type) filter
            if ($this->selectedCategoryId) {
                $query->where('items.cat_id', '=', $this->selectedCategoryId);
            }

            // Always sort by GROUP, BRAND, TYPE for proper grouping (matching PDF structure)
            $query->orderBy('groups.group_name', 'asc')
                  ->orderBy('families.family_name', 'asc')
                  ->orderBy('categories.cat_name', 'asc')
                  ->orderBy('items.item_code', 'asc');

            // Check count first to avoid memory issues
            $itemCount = $query->count();
            
            if ($itemCount === 0) {
                $filterMessages = [];
                
                if ($this->selectedGroupId) {
                    $group = Group::find($this->selectedGroupId);
                    $filterMessages[] = 'Group: ' . ($group->group_name ?? 'Selected');
                }
                
                if ($this->selectedFamilyId) {
                    $family = Family::find($this->selectedFamilyId);
                    $filterMessages[] = 'Brand: ' . ($family->family_name ?? 'Selected');
                }
                
                if ($this->selectedCategoryId) {
                    $category = Category::find($this->selectedCategoryId);
                    $filterMessages[] = 'Type: ' . ($category->cat_name ?? 'Selected');
                }
                
                switch ($this->stockFilter) {
                    case 'gt0':
                        $filterMessages[] = 'Quantity > 0';
                        break;
                    case 'eq0':
                        $filterMessages[] = 'Quantity = 0';
                        break;
                }
                
                if (!empty($filterMessages)) {
                    $this->errorMessage = 'No items found with the selected filters (' . implode(', ', $filterMessages) . ').';
                } else {
                    $this->errorMessage = 'No items available to generate report.';
                }
                
                $this->isGenerating = false;
                return null;
            }
            
            // For PDF, always attempt to generate a single PDF file.
            // Any memory/size problems will be caught and surfaced as an error message.
            if ($this->fileType === 'pdf') {
                $pdfContent = $this->generatePDFContent($query, $itemCount);
                $this->dispatch('download-pdf', [
                    'content' => base64_encode($pdfContent),
                    'filename' => 'inventory_report_' . date('Y-m-d') . '.pdf'
                ]);
            } else {
                // For Excel, we can still load all at once as Excel handles it better
                $items = $query->get();
                $response = $this->downloadExcel($items);
                $this->isGenerating = false;
                return $response;
            }
    
            $this->isGenerating = false;
    
        } catch (\Exception $e) {
            Log::error('Report generation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            $errorMsg = 'Failed to generate report: ' . $e->getMessage();
            if (str_contains($e->getMessage(), 'memory')) {
                $errorMsg = 'Memory error occurred. The dataset may be too large. Please try Excel export or apply filters to reduce the dataset size.';
            }
            
            $this->errorMessage = $errorMsg;
            $this->isGenerating = false;
            session()->flash('error', $errorMsg);
            return null;
        } catch (\Error $e) {
            // Catch fatal errors like memory exhaustion
            Log::error('Fatal error in report generation: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            $errorMsg = 'A fatal error occurred. This usually means the dataset is too large for PDF generation. Please try Excel export instead.';
            $this->errorMessage = $errorMsg;
            $this->isGenerating = false;
            session()->flash('error', $errorMsg);
            return null;
        }
    }
    

    protected function shouldShowTotals()
    {
        // Check if only Cost and Quantity are selected (plus required Stock Code and Stock Description)
        $selectedCols = $this->selectedColumns;
        $requiredCols = ['item_code', 'item_name'];
        $allowedCols = ['qty', 'cost'];
        
        // Remove required columns from check (they're auto-included)
        $otherCols = array_values(array_diff($selectedCols, $requiredCols));
        
        // Should only have qty and cost, nothing else
        sort($otherCols);
        sort($allowedCols);
        
        // Check that we have exactly qty and cost, and no other columns
        return count($otherCols) === 2 && 
               in_array('qty', $otherCols) && 
               in_array('cost', $otherCols) &&
               $otherCols === $allowedCols;
    }

    protected function generatePDFContent($query, $itemCount)
    {
        $originalMemoryLimit = ini_get('memory_limit');
        
        try {
            // Set memory limit to -1 (unlimited) for large PDF generation
            ini_set('memory_limit', '-1');
            
            Log::info('Starting PDF generation', ['item_count' => $itemCount, 'memory_limit' => 'unlimited']);
            
            // Get items directly - no chunking, let Eloquent handle it efficiently
            $items = $query->get();
            
            // Build columns array - always include item_code and item_name
            $finalColumns = array_unique(array_merge(
                ['item_code', 'item_name'],
                $this->selectedColumns
            ));
            $columnsForView = array_intersect_key($this->availableColumns, array_flip($finalColumns));
            
            // Get company profile
            $companyProfile = CompanyProfile::first();
            
            // Check if we should show totals
            $showTotals = $this->shouldShowTotals();
            $grandTotal = 0;
            
            if ($showTotals) {
                // Calculate grand total
                foreach ($items as $item) {
                    $qty = $item->qty ?? 0;
                    $cost = $item->cost ?? 0;
                    $grandTotal += ($qty * $cost);
                }
            }
            
            // Minimal dompdf options for maximum efficiency
            $options = [
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => false,
                'isPhpEnabled' => true, // Enable PHP for page numbering
                'defaultFont' => 'Arial',
                'dpi' => 72,
                'isJavascriptEnabled' => false,
                'fontCache' => sys_get_temp_dir(),
                'chroot' => base_path(),
            ];
            
            // Always use grouping if requested, but optimize for large datasets
            $useGrouping = $this->showGrouping;
            
            $pdf = PDF::loadView('reports.items', [
                'items' => $items,
                'columns' => $columnsForView,
                'companyProfile' => $companyProfile,
                'useGrouping' => $useGrouping,
                'showTotals' => $showTotals,
                'grandTotal' => $grandTotal
            ])->setPaper('a4', 'portrait')
              ->setOptions($options);

            $pdfContent = $pdf->output();
            
            // Restore memory limit only if current usage allows it
            $currentUsage = memory_get_usage(true);
            $originalBytes = $this->convertToBytes($originalMemoryLimit);
            if ($currentUsage < $originalBytes) {
                ini_set('memory_limit', $originalMemoryLimit);
            }
            
            return $pdfContent;
        } catch (\Exception $e) {
            // Don't try to restore memory limit on error - might fail
            Log::error('PDF generation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'item_count' => $itemCount,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e;
        } catch (\Error $e) {
            // Don't try to restore memory limit on error - might fail
            Log::error('Fatal error in PDF generation: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'item_count' => $itemCount,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw new \Exception('PDF generation failed: ' . $e->getMessage());
        }
    }

    protected function generateHTMLContent($query, $itemCount)
    {
        try {
            Log::info('Starting HTML generation for large dataset', ['item_count' => $itemCount]);
            
            // Get items directly
            $items = $query->get();
            
            // Build columns array
            $finalColumns = array_unique(array_merge(
                ['item_code', 'item_name'],
                $this->selectedColumns
            ));
            $columnsForView = array_intersect_key($this->availableColumns, array_flip($finalColumns));
            
            // Get company profile
            $companyProfile = CompanyProfile::first();
            
            // Check if we should show totals
            $showTotals = $this->shouldShowTotals();
            $grandTotal = 0;
            
            if ($showTotals) {
                // Calculate grand total
                foreach ($items as $item) {
                    $qty = $item->qty ?? 0;
                    $cost = $item->cost ?? 0;
                    $grandTotal += ($qty * $cost);
                }
            }
            
            // Generate HTML content
            $htmlContent = view('reports.items-html', [
                'items' => $items,
                'columns' => $columnsForView,
                'companyProfile' => $companyProfile,
                'useGrouping' => $this->showGrouping,
                'showTotals' => $showTotals,
                'grandTotal' => $grandTotal
            ])->render();
            
            return $htmlContent;
        } catch (\Exception $e) {
            Log::error('HTML generation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'item_count' => $itemCount,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }
    
    private function convertToBytes($val)
    {
        if ($val === '-1' || $val === -1) {
            return PHP_INT_MAX;
        }
        
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        $val = (int)$val;
        
        switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        
        return $val;
    }

    protected function downloadExcel($items)
    {
        try {
            $columnLabels = array_intersect_key($this->availableColumns, array_flip($this->selectedColumns));
            $showTotals = $this->shouldShowTotals();
            return Excel::download(
                new ItemsExport($items, $this->selectedColumns, $columnLabels, $this->showGrouping, $showTotals), 
                'inventory_report_' . date('Y-m-d') . '.xlsx'
            );
        } catch (\Exception $e) {
            throw new \Exception('Excel generation failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // Load groups, families, and categories for filter dropdowns
        $groups = Group::orderBy('group_name')->get();
        $families = Family::orderBy('family_name')->get();
        $categories = Category::orderBy('cat_name')->get();
        
        return view('livewire.report', [
            'groups' => $groups,
            'families' => $families,
            'categories' => $categories,
        ])->layout('layouts.app');
    }
}