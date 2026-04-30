<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Item;
use App\Models\CompanyProfile;
use App\Models\Family;
use App\Models\Category;
use App\Models\Group;
use App\Models\Customer;
use App\Models\Supplier;
use App\Jobs\GenerateTransactionPdfReport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionsExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TransactionReport extends Component
{
    public $fileType = 'pdf';
    public $selectedColumns = [];
    public $isGenerating = false;
    public $errorMessage = '';
    public $startDate;
    public $endDate;
    public $selectedTransactionType = 'all';
    public $stockFilter = 'all'; // all | gt0 | eq0
    public $selectedGroupId = null;
    public $selectedFamilyId = null;
    public $selectedCategoryId = null;
    public $selectedCompanyId = null; // Format: "customer_123" or "supplier_456"
    public $selectedCompanyName = null; // Human-readable name for header/filters
    public $companySearchTerm = '';
    public $companySearchResults = [];
    public $companySearchCustomers = [];
    public $companySearchSuppliers = [];
    public $reportHistory = [];
    public $reportJobToken = null;
    public $reportStatusMessage = '';
    public $reportProgress = 0;
    public $reportDownloadUrl = null;
    
    public $availableColumns = [
        'created_at' => 'Transaction Time',
        'item_code' => 'Item Code',
        'item_name' => 'Item Name',
        'qty_on_hand' => 'Quantity on Hand',
        'qty_before' => 'Transaction Quantity Before',
        'qty_after' => 'Transaction Quantity After',
        'transaction_qty' => 'Transaction Quantity',
        'transaction_type' => 'Transaction Type',
        'source_type' => 'Source Type',
        'source_doc_num' => 'Source Document Number',
        'username' => 'User',
        'batch_num' => 'Batch Number'
    ];

    public $transactionTypes = [
        'all' => 'All Transactions',
        'Stock In' => 'Stock In',
        'Stock Out' => 'Stock Out'
    ];

    protected $rules = [
        'fileType' => 'required|in:pdf,excel',
        'selectedColumns' => 'required|array|min:1',
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'selectedTransactionType' => 'required|in:all,Stock In,Stock Out',
        'stockFilter' => 'required|in:all,gt0,eq0'
    ];

    public function mount()
    {
        $this->selectedColumns = ['item_code', 'item_name', 'created_at', 'qty_on_hand', 'transaction_type', 'transaction_qty'];
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
        $this->stockFilter = 'all';
        $this->selectedGroupId = null;
        $this->selectedFamilyId = null;
        $this->selectedCategoryId = null;
        $this->companySearchTerm = '';
        $this->companySearchResults = [];
        $this->companySearchCustomers = [];
        $this->companySearchSuppliers = [];
        $this->reportHistory = session('transaction_report_history', []);
        $this->cleanupExpiredReports();

        $lastToken = session('transaction_report_last_token');
        if ($lastToken) {
            $this->reportJobToken = $lastToken;
            $this->checkReportStatus();
        }
    }

    public function searchCompanies()
    {
        if (!empty($this->companySearchTerm)) {
            $searchTerm = '%' . $this->companySearchTerm . '%';
            
            $customers = Customer::where('cust_name', 'like', $searchTerm)
                ->orWhere('account', 'like', $searchTerm)
                ->orderBy('cust_name', 'asc')
                ->limit(20)
                ->get()
                ->map(function ($customer) {
                    return [
                        'id' => 'customer_' . $customer->id,
                        'name' => $customer->cust_name,
                        'type' => 'Customer'
                    ];
                });
            
            $suppliers = Supplier::where('sup_name', 'like', $searchTerm)
                ->orWhere('account', 'like', $searchTerm)
                ->orderBy('sup_name', 'asc')
                ->limit(20)
                ->get()
                ->map(function ($supplier) {
                    return [
                        'id' => 'supplier_' . $supplier->id,
                        'name' => $supplier->sup_name,
                        'type' => 'Supplier'
                    ];
                });
            
            $this->companySearchCustomers = $customers->values()->toArray();
            $this->companySearchSuppliers = $suppliers->values()->toArray();
            $this->companySearchResults = $customers->concat($suppliers)->sortBy('name')->values()->toArray();
        } else {
            $this->companySearchResults = [];
            $this->companySearchCustomers = [];
            $this->companySearchSuppliers = [];
        }
    }

    public function selectCompany($companyId)
    {
        $this->selectedCompanyId = $companyId;
        $this->companySearchTerm = '';
        $this->companySearchResults = [];
        $this->companySearchCustomers = [];
        $this->companySearchSuppliers = [];
    }

    public function clearCompany()
    {
        $this->selectedCompanyId = null;
        $this->companySearchTerm = '';
        $this->companySearchResults = [];
        $this->companySearchCustomers = [];
        $this->companySearchSuppliers = [];
    }

    public function generateReport()
    {
        try {
            $this->validate();
            $this->isGenerating = true;
            $this->errorMessage = '';
            $this->reportDownloadUrl = null;
            $this->reportStatusMessage = '';
            $this->reportProgress = 0;
            $this->reportJobToken = null;
    
            // Always get all items that match the item-level filters (group / family / category),
            // so that items with no IN/OUT in the selected period can still appear in the report.
            $itemsQuery = Item::query()
                ->leftJoin('categories', 'items.cat_id', '=', 'categories.id')
                ->leftJoin('families', 'items.family_id', '=', 'families.id')
                ->leftJoin('groups', 'items.group_id', '=', 'groups.id');
            
            if ($this->selectedGroupId) {
                $itemsQuery->where('items.group_id', '=', $this->selectedGroupId);
            }
            
            if ($this->selectedFamilyId) {
                $itemsQuery->where('items.family_id', '=', $this->selectedFamilyId);
            }
            
            if ($this->selectedCategoryId) {
                $itemsQuery->where('items.cat_id', '=', $this->selectedCategoryId);
            }
            
            $allItems = $itemsQuery->select([
                'items.id as item_id',
                'items.item_code',
                'items.item_name',
                'items.um',
                'items.qty',
                'items.group_id',
                'items.family_id',
                'items.cat_id',
                'groups.group_name',
                'families.family_name',
                'categories.cat_name'
            ])->get();
            
            if ($allItems->isEmpty()) {
                throw new \Exception('No items found for the selected filters.');
            }
    
            // Get all transactions in the date range
            $transactionQuery = Transaction::query();
            
            // Apply date filters
            if ($this->startDate) {
                $transactionQuery->whereDate('transactions.created_at', '>=', $this->startDate);
            }
            if ($this->endDate) {
                $transactionQuery->whereDate('transactions.created_at', '<=', $this->endDate);
            }

            // Apply transaction type filter
            if ($this->selectedTransactionType !== 'all') {
                $transactionQuery->where('transactions.transaction_type', $this->selectedTransactionType);
            }
    
            // Join with items and apply item filters
            $transactionQuery->leftJoin('items', 'transactions.item_id', '=', 'items.id')
                  ->leftJoin('categories', 'items.cat_id', '=', 'categories.id')
                  ->leftJoin('families', 'items.family_id', '=', 'families.id')
                  ->leftJoin('groups', 'items.group_id', '=', 'groups.id');

            // Apply Group filter
            if ($this->selectedGroupId) {
                $transactionQuery->where('items.group_id', '=', $this->selectedGroupId);
            }

            // Apply Family filter
            if ($this->selectedFamilyId) {
                $transactionQuery->where('items.family_id', '=', $this->selectedFamilyId);
            }

            // Apply Category filter
            if ($this->selectedCategoryId) {
                $transactionQuery->where('items.cat_id', '=', $this->selectedCategoryId);
            }

            // Apply Company filter (through DeliveryOrders for customers or PurchaseOrders for suppliers)
            if ($this->selectedCompanyId) {
                if (str_starts_with($this->selectedCompanyId, 'customer_')) {
                    $customerId = str_replace('customer_', '', $this->selectedCompanyId);
                    $transactionQuery->whereHas('deliveryOrder', function ($subQuery) use ($customerId) {
                        $subQuery->where('cust_id', $customerId);
                    });
                } elseif (str_starts_with($this->selectedCompanyId, 'supplier_')) {
                    $supplierId = str_replace('supplier_', '', $this->selectedCompanyId);
                    $transactionQuery->whereHas('purchaseOrder', function ($subQuery) use ($supplierId) {
                        $subQuery->where('sup_id', $supplierId);
                    });
                }
            }

            // Get all transactions
            $transactions = $transactionQuery->select([
                'transactions.item_id',
                'transactions.qty_before',
                'transactions.qty_after',
                'transactions.transaction_qty',
                'transactions.transaction_type',
                'transactions.created_at',
                'items.item_code',
                'items.item_name',
                'items.um',
                'items.group_id',
                'items.family_id',
                'items.cat_id',
                'groups.group_name',
                'families.family_name',
                'categories.cat_name'
            ])->orderBy('transactions.created_at', 'asc')->get();

            // Initialize item balances - start with all items that match the filters
            $itemBalances = [];
            
            // Initialize all items with zero transactions, using the balance
            // before the selected date range as B/F.
            foreach ($allItems as $item) {
                $startDateCarbon = $this->startDate
                    ? Carbon::parse($this->startDate)->startOfDay()
                    : null;

                $lastTransactionBefore = null;
                if ($startDateCarbon) {
                    $lastTransactionBefore = Transaction::where('item_id', $item->item_id)
                        ->where('created_at', '<', $startDateCarbon)
                        ->orderBy('created_at', 'desc')
                        ->first();
                }
                
                $bf = $lastTransactionBefore ? $lastTransactionBefore->qty_after : ($item->qty ?? 0);
                
                $itemBalances[$item->item_id] = [
                    'item_id' => $item->item_id,
                    'item_code' => $item->item_code,
                    'item_name' => $item->item_name,
                    'um' => $item->um ?? 'PCS',
                    'group_name' => $item->group_name ?? '',
                    'family_name' => $item->family_name ?? '',
                    'cat_name' => $item->cat_name ?? '',
                    'bf' => $bf,
                    'in' => 0,
                    'out' => 0,
                    'balance' => $bf,
                    'has_transactions' => false,
                ];
            }
            
            // Process transactions
            foreach ($transactions as $transaction) {
                $itemId = $transaction->item_id;
                
                if (!isset($itemBalances[$itemId])) {
                    // Initialize item balance - B/F is the qty_before of the first transaction
                    $itemBalances[$itemId] = [
                        'item_id' => $itemId,
                        'item_code' => $transaction->item_code,
                        'item_name' => $transaction->item_name,
                        'um' => $transaction->um ?? 'PCS',
                        'group_name' => $transaction->group_name ?? '',
                        'family_name' => $transaction->family_name ?? '',
                        'cat_name' => $transaction->cat_name ?? '',
                        'bf' => $transaction->qty_before ?? 0, // Balance Forward (first transaction's qty_before)
                        'in' => 0,
                        'out' => 0,
                        'balance' => 0,
                        'has_transactions' => false,
                    ];
                }
                
                // Calculate IN and OUT
                if ($transaction->transaction_type === 'Stock In') {
                    $itemBalances[$itemId]['in'] += abs($transaction->transaction_qty ?? 0);
                } elseif ($transaction->transaction_type === 'Stock Out') {
                    $itemBalances[$itemId]['out'] += abs($transaction->transaction_qty ?? 0);
                }

                // Mark that this item has at least one transaction in the period
                $itemBalances[$itemId]['has_transactions'] = true;
            }
            
            // Calculate final balance for each item
            foreach ($itemBalances as &$balance) {
                $balance['balance'] = $balance['bf'] + $balance['in'] - $balance['out'];
            }
            
            // Convert to collection and sort
            $stockBalances = collect($itemBalances)->values();
            
            // Apply stock filter and visibility rules
            if ($this->stockFilter === 'gt0') {
                $stockBalances = $stockBalances->filter(function ($item) {
                    return $item['balance'] > 0;
                });
            } elseif ($this->stockFilter === 'eq0') {
                $stockBalances = $stockBalances->filter(function ($item) {
                    return $item['balance'] == 0;
                });
            } else {
                // For "ALL" stock filter:
                //  - Show all items whose final balance is not zero
                //  - Also show items with balance == 0 IF they had any transactions in the period
                $stockBalances = $stockBalances->filter(function ($item) {
                    return $item['balance'] != 0 || !empty($item['has_transactions']);
                });
            }

            // If a company filter is applied, restrict to items that actually had
            // at least one transaction for that company in the selected period.
            // (Because only those items are truly "related" to the selected company.)
            if ($this->selectedCompanyId) {
                $stockBalances = $stockBalances->filter(function ($item) {
                    return !empty($item['has_transactions']);
                });
            }
            
            // Sort by Group, Family, Category, Item Code
            $stockBalances = $stockBalances->sortBy([
                ['group_name', 'asc'],
                ['family_name', 'asc'],
                ['cat_name', 'asc'],
                ['item_code', 'asc']
            ])->values();
    
            if ($stockBalances->isEmpty()) {
                throw new \Exception('No data available for the selected filters.');
            }
    
            if ($this->fileType === 'pdf') {
                $this->reportJobToken = (string) Str::uuid();
                $queuedAt = now()->toDateTimeString();
                $filters = $this->buildFilterSummary();
                $context = $this->buildPdfContext();

                Cache::put($this->cacheKey($this->reportJobToken), [
                    'status' => 'queued',
                    'message' => 'Report queued. Please wait...',
                    'progress' => 5,
                    'queued_at' => $queuedAt,
                    'filters' => $filters,
                ], now()->addDays(7));

                session(['transaction_report_last_token' => $this->reportJobToken]);
                $this->upsertReportHistory($this->reportJobToken, [
                    'id' => $this->reportJobToken,
                    'status' => 'queued',
                    'message' => 'Report queued. Please wait...',
                    'progress' => 5,
                    'queued_at' => $queuedAt,
                    'updated_at' => $queuedAt,
                    'filters' => $filters,
                    'file_type' => 'pdf',
                ]);

                GenerateTransactionPdfReport::dispatch(
                    token: $this->reportJobToken,
                    stockBalances: $stockBalances->values()->all(),
                    context: $context
                );

                $this->reportStatusMessage = 'PDF report is generating in background...';
                $this->reportProgress = 10;
                $this->isGenerating = false;
                return null;
            }

            $response = $this->downloadExcel($stockBalances);
            $this->isGenerating = false;
            return $response;
    
        } catch (\Exception $e) {
            Log::error('Report generation failed: ' . $e->getMessage());
            $this->errorMessage = 'Failed to generate report: ' . $e->getMessage();
            $this->isGenerating = false;
            return null;
        }
    }

    protected function downloadExcel($transactions)
    {
        try {
            return Excel::download(
                new TransactionsExport($transactions, $this->selectedColumns), 
                'inventory_report_' . date('Y-m-d') . '.xlsx'
            );
        } catch (\Exception $e) {
            throw new \Exception('Excel generation failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $groups = Group::orderBy('group_name')->get();
        $families = Family::orderBy('family_name')->get();
        $categories = Category::orderBy('cat_name')->get();
        
        // Get selected company name for display
        $selectedCompanyName = null;
        if ($this->selectedCompanyId) {
            if (str_starts_with($this->selectedCompanyId, 'customer_')) {
                $customerId = str_replace('customer_', '', $this->selectedCompanyId);
                $customer = Customer::find($customerId);
                $selectedCompanyName = $customer ? $customer->cust_name : null;
            } elseif (str_starts_with($this->selectedCompanyId, 'supplier_')) {
                $supplierId = str_replace('supplier_', '', $this->selectedCompanyId);
                $supplier = Supplier::find($supplierId);
                $selectedCompanyName = $supplier ? $supplier->sup_name : null;
            }
        }

        // Keep the public property in sync so downloadPDF can reuse it.
        $this->selectedCompanyName = $selectedCompanyName;
        
        return view('livewire.transaction-report', [
            'groups' => $groups,
            'families' => $families,
            'categories' => $categories,
            'selectedCompanyName' => $selectedCompanyName,
            'availableReports' => $this->getAvailableReports(),
        ])->layout('layouts.app');
    }

    public function checkReportStatus()
    {
        if (!$this->reportJobToken) {
            return;
        }

        $statusData = Cache::get($this->cacheKey($this->reportJobToken));
        if (!$statusData) {
            $this->reportStatusMessage = 'Report status expired. Please generate again.';
            $this->reportProgress = 0;
            $this->isGenerating = false;
            return;
        }

        $status = $statusData['status'] ?? 'queued';
        $this->reportStatusMessage = $statusData['message'] ?? '';
        $this->reportProgress = (int) ($statusData['progress'] ?? 10);
        $updatedAt = now()->toDateTimeString();

        $historyPayload = [
            'id' => $this->reportJobToken,
            'status' => $status,
            'message' => $this->reportStatusMessage,
            'progress' => $this->reportProgress,
            'queued_at' => $statusData['queued_at'] ?? $updatedAt,
            'updated_at' => $updatedAt,
            'filters' => $statusData['filters'] ?? [],
            'file_type' => 'pdf',
        ];

        if ($status === 'ready') {
            $this->reportDownloadUrl = route('transaction-report.download', ['token' => $this->reportJobToken]);
            $this->reportProgress = 100;
            $this->isGenerating = false;
            $historyPayload['download_url'] = $this->reportDownloadUrl;
            $historyPayload['progress'] = 100;
        }

        if ($status === 'failed') {
            $this->errorMessage = $statusData['message'] ?? 'PDF generation failed.';
            $this->reportProgress = 0;
            $this->isGenerating = false;
            $historyPayload['progress'] = 0;
        }

        $this->upsertReportHistory($this->reportJobToken, $historyPayload);
    }

    private function buildFilterSummary(): array
    {
        $groupName = 'All Groups';
        $familyName = 'All Families';
        $categoryName = 'All Categories';

        if ($this->selectedGroupId) {
            $groupName = Group::find($this->selectedGroupId)->group_name ?? 'Selected Group';
        }
        if ($this->selectedFamilyId) {
            $familyName = Family::find($this->selectedFamilyId)->family_name ?? 'Selected Family';
        }
        if ($this->selectedCategoryId) {
            $categoryName = Category::find($this->selectedCategoryId)->cat_name ?? 'Selected Category';
        }

        return [
            'start_date' => $this->startDate ?: 'N/A',
            'end_date' => $this->endDate ?: 'N/A',
            'transaction_type' => $this->selectedTransactionType ?: 'all',
            'stock_filter' => $this->stockFilter ?: 'all',
            'group' => $groupName,
            'family' => $familyName,
            'category' => $categoryName,
            'company' => $this->selectedCompanyName ?: 'All Companies',
        ];
    }

    private function buildPdfContext(): array
    {
        $groupName = $this->selectedGroupId ? Group::find($this->selectedGroupId)->group_name ?? 'ALL' : 'ALL';
        $familyName = $this->selectedFamilyId ? Family::find($this->selectedFamilyId)->family_name ?? 'ALL' : 'ALL';
        $categoryName = $this->selectedCategoryId ? Category::find($this->selectedCategoryId)->cat_name ?? 'ALL' : 'ALL';
        $stockFilterName = $this->stockFilter === 'gt0' ? '> 0' : ($this->stockFilter === 'eq0' ? '= 0' : 'ALL');

        $companyName = $this->selectedCompanyName ?: 'ALL';
        if ($companyName === 'ALL' && $this->selectedCompanyId) {
            if (str_starts_with($this->selectedCompanyId, 'customer_')) {
                $customerId = str_replace('customer_', '', $this->selectedCompanyId);
                $customer = Customer::find($customerId);
                $companyName = $customer ? $customer->cust_name : 'ALL';
            } elseif (str_starts_with($this->selectedCompanyId, 'supplier_')) {
                $supplierId = str_replace('supplier_', '', $this->selectedCompanyId);
                $supplier = Supplier::find($supplierId);
                $companyName = $supplier ? $supplier->sup_name : 'ALL';
            }
        }

        return [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'groupName' => $groupName,
            'familyName' => $familyName,
            'categoryName' => $categoryName,
            'companyName' => $companyName,
            'stockFilter' => $stockFilterName,
        ];
    }

    private function upsertReportHistory(string $reportId, array $payload): void
    {
        $history = collect($this->reportHistory);
        $index = $history->search(fn ($item) => ($item['id'] ?? null) === $reportId);

        if ($index === false) {
            $history->prepend($payload);
        } else {
            $existing = $history->get($index);
            $history->put($index, array_merge($existing, $payload));
        }

        $this->reportHistory = $history->take(10)->values()->all();
        session(['transaction_report_history' => $this->reportHistory]);
    }

    private function cleanupExpiredReports(): void
    {
        $disk = Storage::disk('local');
        if (!$disk->exists('reports')) {
            return;
        }

        $expireBefore = now()->subDays(7)->timestamp;
        foreach ($disk->files('reports') as $file) {
            try {
                $basename = basename($file);
                if (!str_starts_with($basename, 'transaction_report_')) {
                    continue;
                }

                if ($disk->lastModified($file) < $expireBefore) {
                    $disk->delete($file);
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to clean up transaction report file', [
                    'file' => $file,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }

    private function getAvailableReports(): array
    {
        $this->cleanupExpiredReports();
        $disk = Storage::disk('local');
        if (!$disk->exists('reports')) {
            return [];
        }

        return collect($disk->files('reports'))
            ->map(fn ($file) => basename($file))
            ->filter(fn ($filename) => str_starts_with($filename, 'transaction_report_') && str_ends_with(strtolower($filename), '.pdf'))
            ->map(function ($filename) use ($disk) {
                $path = 'reports/' . $filename;
                $lastModified = $disk->lastModified($path);

                return [
                    'filename' => $filename,
                    'generated_at' => date('Y-m-d H:i:s', $lastModified),
                    'size_kb' => round($disk->size($path) / 1024, 1),
                    'download_url' => route('report.download-file', ['filename' => $filename]),
                ];
            })
            ->sortByDesc('generated_at')
            ->values()
            ->all();
    }

    private function cacheKey(string $token): string
    {
        return 'transaction_report_pdf:' . $token;
    }
}