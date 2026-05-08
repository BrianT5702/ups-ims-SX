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

    /**
     * Per-request cache for the DO posting-event index.
     * @var array<string, array{visible_ids: array<int,int>, event_qty: array<int,float>, is_latest: array<int,bool>}>
     */
    private array $doEventsCache = [];

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
        // ledger so In/Out and Balance always tally on screen. We pass it as a
        // separate map [tx_id => balance] for the view to render.
        $displayBalances = $this->recomputeDisplayBalances($transactions);

        // Mark rows that were superseded by a later edit / repost on the same
        // (DO, item) so the view can render them in a muted style.
        $supersededDoMap = $this->buildSupersededDoMap($transactions);

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
            'endDate' => $this->endDate,
            'displayBalances' => $displayBalances,
            'supersededDoMap' => $supersededDoMap,
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
        $displayBalances = $this->recomputeDisplayBalances($transactions);

        // Mark rows that were superseded by a later edit / repost on the same
        // (DO, item) so the view can render them in a muted style.
        $supersededDoMap = $this->buildSupersededDoMap($transactions);

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
            'endDate' => $this->endDate,
            'displayBalances' => $displayBalances,
            'supersededDoMap' => $supersededDoMap,
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

    /**
     * Build a "posting-event" index for DO Stock Out rows.
     *
     * A posting event for a (source_doc_num, item_id) pair is a contiguous run
     * of DO `Stock Out` rows (ordered by id) that are NOT separated by a
     * reversal `Stock In` row (`DO Reversal` / `DO Status Reversal` /
     * `DO Delta Reversal` / `DO Draft Delta`). Each event represents one real
     * posting moment from the user's perspective:
     *
     *   - Original Completed save                -> event 1
     *   - <reversal Stock In(s) from edit>        (event boundary, hidden)
     *   - Re-deduct after editing qty             -> event 2
     *
     * For each event we choose ONE representative row to display: MAX(id), so
     * the row's `qty_after` reflects the running balance right after the whole
     * batch of Stock Outs for that posting event. Older events for the same
     * (DO, item) are flagged "superseded" so the view can render them in a
     * muted / yellow style.
     *
     * Cached per request (per filter scope) to avoid repeated scans.
     *
     * @return array{visible_ids: array<int,int>, event_qty: array<int,float>, is_latest: array<int,bool>}
     */
    private function buildDoStockOutEvents(): array
    {
        $cacheKey = ($this->filterItemId ? 'item:' . $this->filterItemId : 'no-item')
            . '|' . ($this->selectedGroupId ? 'group:' . $this->selectedGroupId : 'no-group');

        if (isset($this->doEventsCache[$cacheKey])) {
            return $this->doEventsCache[$cacheKey];
        }

        $doSourceTypes = $this->getDoSourceTypes();
        $reversalTypes = $this->getDoReversalSourceTypes();

        $query = Transaction::query()
            ->whereIn('source_type', array_merge($doSourceTypes, $reversalTypes));

        if ($this->filterItemId) {
            $query->where('item_id', $this->filterItemId);
        } elseif ($this->selectedGroupId) {
            $groupId = $this->selectedGroupId;
            $query->whereHas('item', function ($q) use ($groupId) {
                $q->where('group_id', $groupId);
            });
        }

        $rows = $query
            ->orderBy('source_doc_num')
            ->orderBy('item_id')
            ->orderBy('id')
            ->get(['id', 'source_doc_num', 'item_id', 'source_type', 'transaction_type', 'transaction_qty']);

        $visibleIds = [];
        $eventQty = [];
        $isLatest = [];

        $byPair = $rows->groupBy(fn ($r) => ($r->source_doc_num ?? '') . '|' . ($r->item_id ?? ''));

        foreach ($byPair as $pairRows) {
            $events = [];
            $current = null;

            foreach ($pairRows as $row) {
                $isOut = in_array($row->source_type, $doSourceTypes, true)
                    && $row->transaction_type === 'Stock Out';
                $isReversalIn = in_array($row->source_type, $reversalTypes, true)
                    && $row->transaction_type === 'Stock In';

                if ($isOut) {
                    if ($current === null) {
                        $current = ['ids' => [], 'qty' => 0.0];
                    }
                    $current['ids'][] = (int) $row->id;
                    $current['qty'] += abs((float) $row->transaction_qty);
                } elseif ($isReversalIn) {
                    if ($current !== null) {
                        $events[] = $current;
                        $current = null;
                    }
                }
            }

            if ($current !== null) {
                $events[] = $current;
            }

            $eventCount = count($events);
            foreach ($events as $idx => $ev) {
                $repId = max($ev['ids']);
                $visibleIds[] = $repId;
                $eventQty[$repId] = $ev['qty'];
                $isLatest[$repId] = ($idx === $eventCount - 1);
            }
        }

        return $this->doEventsCache[$cacheKey] = [
            'visible_ids' => $visibleIds,
            'event_qty' => $eventQty,
            'is_latest' => $isLatest,
        ];
    }

    private function applyTransactionLogVisibilityRules(Builder $query): Builder
    {
        $doSourceTypes = $this->getDoSourceTypes();
        $reversalTypes = $this->getDoReversalSourceTypes();
        $events = $this->buildDoStockOutEvents();
        $visibleIds = $events['visible_ids'];

        // Hide reversal source types from the grid -- those are noise rows that
        // exist only to keep the ledger consistent. Then for DO source types,
        // show only the representative Stock Out row of each posting event
        // (one row per real shipping moment, with edits showing as their own
        // additional row instead of being silently swallowed into the original).
        return $query->where(function ($q) use ($reversalTypes) {
            $q->whereNotIn('source_type', $reversalTypes);
        })->where(function ($q) use ($doSourceTypes, $visibleIds) {
            $q->whereNotIn('source_type', $doSourceTypes);
            if (!empty($visibleIds)) {
                $q->orWhereIn('id', $visibleIds);
            }
        });
    }

    /**
     * Replace transaction_qty on each visible DO Stock Out row with the SUM of
     * Stock Out qty for that posting event only (so multiple FIFO batch lines
     * shipped together appear as one "Out" number). Reversal Stock Ins are
     * NOT subtracted here -- a reversal ends the event, and the next
     * Stock Out chain starts a new event with its own representative row.
     *
     * @param  iterable<\App\Models\Transaction>  $transactions
     */
    private function aggregateDoStockOutQuantities($transactions): void
    {
        $doSourceTypes = $this->getDoSourceTypes();
        $events = $this->buildDoStockOutEvents();
        $eventQty = $events['event_qty'];

        foreach ($transactions as $tx) {
            if (!in_array($tx->source_type, $doSourceTypes, true)
                || $tx->transaction_type !== 'Stock Out'
            ) {
                continue;
            }

            $id = (int) $tx->id;
            if (isset($eventQty[$id])) {
                $tx->transaction_qty = $eventQty[$id];
            }
        }
    }

    /**
     * @param  iterable<\App\Models\Transaction>  $transactions
     * @return array<int, bool> [transaction_id => true] for rows that were superseded
     *   by a later edit/repost on the same (DO, item).
     */
    private function buildSupersededDoMap($transactions): array
    {
        $doSourceTypes = $this->getDoSourceTypes();
        $events = $this->buildDoStockOutEvents();
        $isLatest = $events['is_latest'];

        $map = [];
        foreach ($transactions as $tx) {
            if (!in_array($tx->source_type, $doSourceTypes, true)
                || $tx->transaction_type !== 'Stock Out'
            ) {
                continue;
            }

            $id = (int) $tx->id;
            if (isset($isLatest[$id]) && $isLatest[$id] === false) {
                $map[$id] = true;
            }
        }
        return $map;
    }

    /**
     * Recompute the Balance (qty_after) shown on each visible row as a running
     * cumulative that matches **physical stock**, using the **complete** ledger
     * for each item (no Transaction Log visibility filter).
     *
     * If we only walked "visible" rows, every DO reversal Stock In (`DO Delta Reversal`,
     * `DO Status Reversal`, etc.) would be skipped — those rows are hidden from the
     * grid but they still affect inventory. Example: edit DO 20→10 after a PO creates
     * a reversal +10; the log would show wrong Balance (e.g. -20 vs actual 0) until
     * we include hidden rows in this walk.
     *
     * We use each row's stored `transaction_qty` as-is (no DO net aggregation here),
     * ordered chronologically, so the running balance matches `items.qty` at the end
     * and behaves correctly at every step.
     *
     * The Out column for DO lines is still adjusted separately by
     * aggregateDoStockOutQuantities() on the paginated collection only.
     *
     * @param  iterable<\App\Models\Transaction>  $pageTransactions
     * @return array<int, float|int>
     */
    private function recomputeDisplayBalances($pageTransactions): array
    {
        $itemIds = collect($pageTransactions)
            ->pluck('item_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($itemIds)) {
            return [];
        }

        // Full ledger — include hidden reversal rows so Balance matches reality.
        $allRows = Transaction::query()
            ->whereIn('item_id', $itemIds)
            ->orderBy('item_id', 'asc')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if ($allRows->isEmpty()) {
            return [];
        }

        // Opening balance (BF) per item = qty_before of that item's earliest
        // transaction in the raw ledger. Unchanged.
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

        foreach ($allRows as $tx) {
            $iid = (int) $tx->item_id;

            if (!array_key_exists($iid, $balances)) {
                $balances[$iid] = isset($bfRows[$iid])
                    ? (float) $bfRows[$iid]->qty_before
                    : 0.0;
            }

            $qty = abs((float) $tx->transaction_qty);
            if ($tx->transaction_type === 'Stock In') {
                $balances[$iid] += $qty;
            } elseif ($tx->transaction_type === 'Stock Out') {
                $balances[$iid] -= $qty;
            }

            $balanceByTxId[(int) $tx->id] = $balances[$iid];
        }

        return $balanceByTxId;
    }
}
