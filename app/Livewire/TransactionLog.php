<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Item;
use App\Models\Group;
use App\Models\Customer;
use App\Models\Supplier;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Carbon\Carbon;

#[Title('UR | Transaction Log')]
class TransactionLog extends Component
{
    use WithPagination;

    public $filterItemId = null;
    public $selectedGroupId = null;
    public $startDate = null;
    public $endDate = null;
    public $searchTerm = '';
    public $sourceTypeFilter = '';
    public $selectedCompanyId = null; // Format: "customer_123" or "supplier_456"
    public $companySearchTerm = '';
    public $companySearchResults = [];
    public $companySearchCustomers = [];
    public $companySearchSuppliers = [];

    // Reset pagination when filters change
    public function updatingSearchTerm()
    {
        $this->resetPage();
    }

    public function updatingStartDate()
    {
        $this->resetPage();
    }

    public function updatingEndDate()
    {
        $this->resetPage();
    }

    public function updatedStartDate($value)
    {
        if ($this->endDate && $value > $this->endDate) {
            $this->endDate = $value;
        }
    }

    public function updatedEndDate($value)
    {
        if ($this->startDate && $value < $this->startDate) {
            $this->endDate = $this->startDate;
            toastr()->error('End date cannot be earlier than start date');
        }
    }

    public function updatingSourceTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingSelectedGroupId()
    {
        $this->resetPage();
    }

    public function updatingSelectedCompanyId()
    {
        $this->resetPage();
    }

    public function mount($itemId = null)
    {
        $this->filterItemId = $itemId;
    }

    public function clearFilters()
    {
        $this->reset([
            'searchTerm', 
            'sourceTypeFilter', 
            'startDate', 
            'endDate',
            'selectedGroupId',
            'selectedCompanyId',
            'companySearchTerm',
            'companySearchResults',
            'companySearchCustomers',
            'companySearchSuppliers'
        ]);
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
        $this->resetPage();
    }

    public function clearCompany()
    {
        $this->selectedCompanyId = null;
        $this->companySearchTerm = '';
        $this->companySearchResults = [];
        $this->companySearchCustomers = [];
        $this->companySearchSuppliers = [];
        $this->resetPage();
    }

    public function render()
    {
        // Special handling: When group is selected AND date range is set, show all items in group
        // even if they have no transactions in the date range
        if ($this->selectedGroupId && $this->startDate && $this->endDate) {
            return $this->renderGroupReport();
        }

        // Original transaction-based query
        $query = Transaction::with('user', 'item', 'deliveryOrder.customerSnapshot', 'deliveryOrder.customer', 'purchaseOrder.supplierSnapshot', 'purchaseOrder.supplier')
            ->when($this->filterItemId, function ($q) {
                return $q->where('item_id', $this->filterItemId);
            })
            ->when($this->selectedGroupId, function ($q) {
                return $q->whereHas('item', function ($subQuery) {
                    $subQuery->where('group_id', $this->selectedGroupId);
                });
            })
            ->when($this->startDate && $this->endDate, function ($q) {
                return $q->whereBetween('created_at', [
                    Carbon::parse($this->startDate)->startOfDay(), 
                    Carbon::parse($this->endDate)->endOfDay()
                ]);
            })
            ->when($this->searchTerm, function ($q) {
                return $q->where(function ($query) {
                    $query->whereHas('item', function ($subQuery) {
                        $subQuery->where('item_code', 'like', '%' . $this->searchTerm . '%')
                                 ->orWhere('item_name', 'like', '%' . $this->searchTerm . '%');
                    })
                    ->orWhere('source_doc_num', 'like', '%' . $this->searchTerm . '%');
                });
            })
            ->when($this->sourceTypeFilter, function ($q) {
                return $q->where('source_type', $this->sourceTypeFilter);
            })
            ->when($this->selectedCompanyId, function ($q) {
                // Parse company filter: "customer_123" or "supplier_456"
                if (str_starts_with($this->selectedCompanyId, 'customer_')) {
                    $customerId = str_replace('customer_', '', $this->selectedCompanyId);
                    return $q->whereHas('deliveryOrder', function ($subQuery) use ($customerId) {
                        $subQuery->where('cust_id', $customerId);
                    });
                } elseif (str_starts_with($this->selectedCompanyId, 'supplier_')) {
                    $supplierId = str_replace('supplier_', '', $this->selectedCompanyId);
                    return $q->whereHas('purchaseOrder', function ($subQuery) use ($supplierId) {
                        $subQuery->where('sup_id', $supplierId);
                    });
                }
                return $q;
            })
            ->orderBy('created_at', 'desc');

        $transactions = $query->paginate(20);

        // Get the item details if filtering by item
        $filteredItem = $this->filterItemId 
            ? Item::findOrFail($this->filterItemId) 
            : null;

        // Prepare source type options
        $sourceTypeOptions = Transaction::distinct('source_type')->pluck('source_type');

        // Get groups for dropdown
        $groups = Group::orderBy('group_name')->get();

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

        // Return the view for rendering
        return view('livewire.transaction-log', [
            'transactions' => $transactions,
            'filteredItem' => $filteredItem,
            'sourceTypeOptions' => $sourceTypeOptions,
            'groups' => $groups,
            'selectedCompanyName' => $selectedCompanyName ?? null,
            'isGroupReportMode' => false,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate
        ])->layout('layouts.app');
    }

    private function renderGroupReport()
    {
        // Get all items in the selected group (paginated)
        $itemsQuery = Item::where('group_id', $this->selectedGroupId)
            ->when($this->searchTerm, function ($q) {
                return $q->where(function ($query) {
                    $query->where('item_code', 'like', '%' . $this->searchTerm . '%')
                          ->orWhere('item_name', 'like', '%' . $this->searchTerm . '%');
                });
            })
            ->orderBy('item_code', 'asc');

        $paginatedItems = $itemsQuery->paginate(20);
        $itemIds = $paginatedItems->pluck('id')->toArray();

        // Get transactions for these items within the date range
        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();

        $transactionsQuery = Transaction::with('user', 'item', 'deliveryOrder.customerSnapshot', 'deliveryOrder.customer', 'purchaseOrder.supplierSnapshot', 'purchaseOrder.supplier')
            ->whereIn('item_id', $itemIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($this->sourceTypeFilter, function ($q) {
                return $q->where('source_type', $this->sourceTypeFilter);
            })
            ->when($this->selectedCompanyId, function ($q) {
                // Parse company filter: "customer_123" or "supplier_456"
                if (str_starts_with($this->selectedCompanyId, 'customer_')) {
                    $customerId = str_replace('customer_', '', $this->selectedCompanyId);
                    return $q->whereHas('deliveryOrder', function ($subQuery) use ($customerId) {
                        $subQuery->where('cust_id', $customerId);
                    });
                } elseif (str_starts_with($this->selectedCompanyId, 'supplier_')) {
                    $supplierId = str_replace('supplier_', '', $this->selectedCompanyId);
                    return $q->whereHas('purchaseOrder', function ($subQuery) use ($supplierId) {
                        $subQuery->where('sup_id', $supplierId);
                    });
                }
                return $q;
            })
            ->orderBy('created_at', 'desc');

        $transactions = $transactionsQuery->get();

        // Group transactions by item_id
        $transactionsByItem = $transactions->groupBy('item_id');

        // Build result array: show all items on current page, with their transactions
        $result = collect();
        $iteration = 0;

        foreach ($paginatedItems as $item) {
            $itemTransactions = $transactionsByItem->get($item->id, collect());
            
            if ($itemTransactions->isEmpty()) {
                // Item has no transactions in date range - show empty row
                $result->push([
                    'type' => 'item_no_transactions',
                    'item' => $item,
                    'iteration' => ++$iteration
                ]);
            } else {
                // Item has transactions - show each transaction
                foreach ($itemTransactions as $transaction) {
                    $result->push([
                        'type' => 'transaction',
                        'item' => $item,
                        'transaction' => $transaction,
                        'iteration' => ++$iteration
                    ]);
                }
            }
        }

        // Create a custom paginator that uses the result collection but maintains pagination info
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $result,
            $paginatedItems->total(), // Total items count
            $paginatedItems->perPage(),
            $paginatedItems->currentPage(),
            ['path' => request()->url(), 'query' => request()->query()]
        );
        $paginator->setCollection($result);

        // Get the item details if filtering by item
        $filteredItem = $this->filterItemId 
            ? Item::findOrFail($this->filterItemId) 
            : null;

        // Prepare source type options
        $sourceTypeOptions = Transaction::distinct('source_type')->pluck('source_type');

        // Get groups for dropdown
        $groups = Group::orderBy('group_name')->get();

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

        return view('livewire.transaction-log', [
            'transactions' => $paginator,
            'filteredItem' => $filteredItem,
            'sourceTypeOptions' => $sourceTypeOptions,
            'groups' => $groups,
            'selectedCompanyName' => $selectedCompanyName ?? null,
            'isGroupReportMode' => true,
            'selectedGroup' => Group::find($this->selectedGroupId),
            'startDate' => $this->startDate,
            'endDate' => $this->endDate
        ])->layout('layouts.app');
    }

    public function redirectToPage($sourceType, $transactionId)
    {
        $transaction = Transaction::findOrFail($transactionId);
        $item_id = $transaction->item_id;
        $sourceDocNum = $transaction->source_doc_num;
    
        switch ($sourceType) {
            case 'PO':
                $purchaseOrder = \App\Models\PurchaseOrder::where('po_num', $sourceDocNum)->first();
                if ($purchaseOrder) {
                    return redirect()->route('purchase-orders.view', ['purchaseOrder' => $purchaseOrder->id]);
                }
                break;
    
            case 'DO':
                $deliveryOrder = \App\Models\DeliveryOrder::where('do_num', $sourceDocNum)->first();
                if ($deliveryOrder) {
                    return redirect()->route('delivery-orders.view', ['deliveryOrder' => $deliveryOrder->id]);
                }
                break;
    
            case 'Batch Adjustment':
                return redirect()->route('items.view', ['item' => $item_id]);
                
            default:
                return redirect()->route('transaction-log.');
        }
    
        return redirect()->route('transaction-log.');
    }
}
