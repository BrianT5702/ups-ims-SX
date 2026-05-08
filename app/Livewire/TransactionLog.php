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
use Illuminate\Database\Eloquent\Builder;

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
            });

        $query = $this->applyTransactionLogVisibilityRules($query)
            ->orderBy('created_at', 'desc');

        $transactions = $query->paginate(20);

        // Replace transaction_qty on the visible DO Stock Out rows with the net qty
        // shipped for that (DO, item) -- summing repeated lines and subtracting any
        // reversal Stock Ins so the In/Out column matches reality.
        $this->aggregateDoStockOutQuantities($transactions);

        // Recompute Balance (qty_after) as a running cumulative over the simplified
        // ledger so In/Out and Balance always tally on screen.
        $this->recomputeDisplayBalances($transactions);

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
            });

        $transactionsQuery = $this->applyTransactionLogVisibilityRules($transactionsQuery)
            ->orderBy('created_at', 'desc');

        $transactions = $transactionsQuery->get();

        // Replace transaction_qty on visible DO Stock Out rows with the net qty
        // shipped for that (DO, item). Same reasoning as in render().
        $this->aggregateDoStockOutQuantities($transactions);

        // Recompute Balance (qty_after) as a running cumulative on the simplified
        // ledger so In/Out and Balance always tally on screen.
        $this->recomputeDisplayBalances($transactions);

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

    private function getDoSourceTypes(): array
    {
        return ['DO', 'Delivery Order'];
    }

    private function getDoReversalSourceTypes(): array
    {
        return ['DO Reversal', 'DO Status Reversal', 'DO Delta Reversal', 'DO Draft Delta'];
    }

    private function applyTransactionLogVisibilityRules(Builder $query): Builder
    {
        $doSourceTypes = $this->getDoSourceTypes();
        $doReversalTypes = $this->getDoReversalSourceTypes();

        // For each (source_doc_num, item_id) under a DO source type, only show ONE
        // representative Stock Out row -- specifically the FIRST one (MIN id) so the
        // row is anchored to the DO's original creation date in the timeline.
        // Reversal source types are hidden from the log entirely; the displayed
        // quantity for the representative row is set by aggregateDoStockOutQuantities()
        // to the net qty actually shipped (sum of all Stock Out minus reversal Stock In)
        // for that DO + item.
        return $query->where(function ($mainQuery) use ($doReversalTypes) {
            $mainQuery->whereNotIn('source_type', $doReversalTypes);
        })->where(function ($mainQuery) use ($doSourceTypes) {
            $mainQuery->whereNotIn('source_type', $doSourceTypes)
                ->orWhereIn('id', function ($subQuery) use ($doSourceTypes) {
                    $subQuery->from('transactions as t2')
                        ->selectRaw('MIN(t2.id)')
                        ->whereIn('t2.source_type', $doSourceTypes)
                        ->where('t2.transaction_type', 'Stock Out')
                        ->groupBy('t2.source_doc_num', 't2.item_id');
                });
        });
    }

    /**
     * For each visible DO Stock Out row, replace transaction_qty with the NET
     * quantity actually shipped for that (source_doc_num, item_id):
     *     net_out = SUM(Stock Out qty)  -  SUM(Stock In qty)
     * across every transaction (visible or hidden) that shares the same DO
     * number and item, restricted to DO + DO-reversal source types. This is
     * what powers the In/Out column.
     *
     * Balance (qty_after) is NOT touched here -- it is recomputed later as a
     * running cumulative by recomputeDisplayBalances() so that Out and Balance
     * always tally on the simplified ledger the user actually sees.
     *
     * @param  iterable<\App\Models\Transaction>  $transactions
     */
    private function aggregateDoStockOutQuantities($transactions): void
    {
        $doSourceTypes = $this->getDoSourceTypes();
        $doReversalTypes = $this->getDoReversalSourceTypes();
        $allDoRelatedTypes = array_merge($doSourceTypes, $doReversalTypes);

        $pairs = [];
        foreach ($transactions as $tx) {
            if (in_array($tx->source_type, $doSourceTypes, true)
                && $tx->transaction_type === 'Stock Out'
                && !empty($tx->source_doc_num)
                && !empty($tx->item_id)
            ) {
                $pairs[$tx->source_doc_num . '|' . $tx->item_id] = [
                    'source_doc_num' => $tx->source_doc_num,
                    'item_id' => $tx->item_id,
                ];
            }
        }

        if (empty($pairs)) {
            return;
        }

        $sums = Transaction::query()
            ->selectRaw(
                'source_doc_num, item_id, '
                . 'SUM(CASE '
                . "WHEN transaction_type = 'Stock Out' THEN ABS(transaction_qty) "
                . "WHEN transaction_type = 'Stock In'  THEN -ABS(transaction_qty) "
                . 'ELSE 0 END) AS net_out'
            )
            ->whereIn('source_type', $allDoRelatedTypes)
            ->where(function ($q) use ($pairs) {
                foreach ($pairs as $pair) {
                    $q->orWhere(function ($qq) use ($pair) {
                        $qq->where('source_doc_num', $pair['source_doc_num'])
                           ->where('item_id', $pair['item_id']);
                    });
                }
            })
            ->groupBy('source_doc_num', 'item_id')
            ->get()
            ->keyBy(fn ($row) => $row->source_doc_num . '|' . $row->item_id);

        foreach ($transactions as $tx) {
            if (!in_array($tx->source_type, $doSourceTypes, true)
                || $tx->transaction_type !== 'Stock Out'
                || empty($tx->source_doc_num)
                || empty($tx->item_id)
            ) {
                continue;
            }

            $key = $tx->source_doc_num . '|' . $tx->item_id;
            if (isset($sums[$key])) {
                $tx->transaction_qty = (int) $sums[$key]->net_out;
            }
        }
    }

    /**
     * Recompute the Balance (qty_after) shown on each visible row as a running
     * cumulative over the SIMPLIFIED ledger (the rows the user is allowed to
     * see, after DO Stock Out qty has been collapsed to its net by
     * aggregateDoStockOutQuantities()). This guarantees that Out and Balance
     * tally on screen, no matter how many reversal/re-issue cycles a DO went
     * through.
     *
     * For each item that appears on the current page we:
     *   1. Load every visible transaction for that item across its full history
     *      (NOT just the page) sorted chronologically.
     *   2. Apply the DO Out aggregation so each DO Stock Out row carries its
     *      net quantity.
     *   3. Walk the rows from oldest to newest, starting with BF = qty_before
     *      of the item's earliest transaction (typically 0 for new items, or
     *      the imported opening stock), adding Stock In qty and subtracting
     *      Stock Out qty as we go.
     *   4. Project the computed balance onto the rows that actually live on
     *      the current page.
     *
     * @param  iterable<\App\Models\Transaction>  $pageTransactions
     */
    private function recomputeDisplayBalances($pageTransactions): void
    {
        $itemIds = collect($pageTransactions)
            ->pluck('item_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($itemIds)) {
            return;
        }

        // Pull the full visible ledger for these items.
        $visibleQuery = Transaction::query()->whereIn('item_id', $itemIds);
        $visibleQuery = $this->applyTransactionLogVisibilityRules($visibleQuery);
        $allVisible = $visibleQuery
            ->orderBy('item_id', 'asc')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if ($allVisible->isEmpty()) {
            return;
        }

        // Make sure DO Stock Out rows in this set carry the net qty before we
        // walk them; otherwise the running balance would double-count repeated
        // DO lines or ignore reversals.
        $this->aggregateDoStockOutQuantities($allVisible);

        // Opening balance (BF) per item = qty_before of that item's earliest
        // transaction in the raw ledger (visible or hidden). This is the only
        // anchor that matches reality at t = -infinity.
        $bfRows = Transaction::query()
            ->whereIn('item_id', $itemIds)
            ->whereIn('id', function ($q) use ($itemIds) {
                $q->from('transactions')
                  ->selectRaw('MIN(id)')
                  ->whereIn('item_id', $itemIds)
                  ->groupBy('item_id');
            })
            ->get(['id', 'item_id', 'qty_before'])
            ->keyBy('item_id');

        $balances = [];
        $balanceByTxId = [];

        foreach ($allVisible as $tx) {
            $iid = (int) $tx->item_id;

            if (!array_key_exists($iid, $balances)) {
                $balances[$iid] = isset($bfRows[$iid])
                    ? (int) $bfRows[$iid]->qty_before
                    : 0;
            }

            $qty = abs((int) $tx->transaction_qty);
            if ($tx->transaction_type === 'Stock In') {
                $balances[$iid] += $qty;
            } elseif ($tx->transaction_type === 'Stock Out') {
                $balances[$iid] -= $qty;
            }

            $balanceByTxId[(int) $tx->id] = $balances[$iid];
        }

        foreach ($pageTransactions as $tx) {
            $key = (int) $tx->id;
            if (isset($balanceByTxId[$key])) {
                $tx->qty_after = $balanceByTxId[$key];
            }
        }
    }
}
