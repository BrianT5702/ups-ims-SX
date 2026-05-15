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
use Illuminate\Support\Facades\Cache;

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
        $this->endDate = $this->defaultEndDateForGmtPlus8();
    }

    /**
     * "To date" filter default: today in GMT+8 (business locale).
     */
    private function defaultEndDateForGmtPlus8(): string
    {
        return Carbon::now('Asia/Singapore')->format('Y-m-d');
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
        $this->endDate = $this->defaultEndDateForGmtPlus8();
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

        // Original transaction-based query (join DO/PO dates for filter + sort)
        $query = Transaction::with('item', 'deliveryOrder.customerSnapshot', 'deliveryOrder.customer', 'purchaseOrder.supplierSnapshot', 'purchaseOrder.supplier')
            ->withLogDocDateJoins()
            ->when($this->filterItemId, function ($q) {
                return $q->where('transactions.item_id', $this->filterItemId);
            })
            ->when($this->selectedGroupId, function ($q) {
                return $q->whereHas('item', function ($subQuery) {
                    $subQuery->where('group_id', $this->selectedGroupId);
                });
            })
            ->when($this->startDate && $this->endDate, function ($q) {
                $q->whereLogDisplayDateBetween(
                    Carbon::parse($this->startDate)->startOfDay(),
                    Carbon::parse($this->endDate)->endOfDay()
                );
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
            ->orderByLogDisplayDate('desc');

        $transactions = $query->paginate(20);

        // Replace transaction_qty on the visible DO Stock Out rows with the net qty
        // shipped for that (DO, item) -- summing repeated lines and subtracting any
        // reversal Stock Ins so the In/Out column matches reality.
        $this->aggregateDoStockOutQuantities($transactions);

        // Recompute Balance (qty_after) as a running cumulative over the simplified
        // ledger so In/Out and Balance always tally on screen. We pass it as a
        // separate map [tx_id => balance] for the view to render.
        $displayBalances = $this->recomputeDisplayBalances($transactions);

        // Get the item details if filtering by item
        $filteredItem = $this->filterItemId 
            ? Item::findOrFail($this->filterItemId) 
            : null;

        $sourceTypeOptions = $this->cachedSourceTypeOptions();
        $groups = $this->cachedGroupsForFilter();

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

        $transactionsQuery = Transaction::with('item', 'deliveryOrder.customerSnapshot', 'deliveryOrder.customer', 'purchaseOrder.supplierSnapshot', 'purchaseOrder.supplier')
            ->withLogDocDateJoins()
            ->whereIn('transactions.item_id', $itemIds)
            ->whereLogDisplayDateBetween($startDate, $endDate)
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
            ->orderByLogDisplayDate('desc');

        $transactions = $transactionsQuery->get();

        // Replace transaction_qty on visible DO Stock Out rows with the net qty
        // shipped for that (DO, item). Same reasoning as in render().
        $this->aggregateDoStockOutQuantities($transactions);

        // Recompute Balance (qty_after) as a running cumulative on the simplified
        // ledger so In/Out and Balance always tally on screen.
        $displayBalances = $this->recomputeDisplayBalances($transactions);

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

        $sourceTypeOptions = $this->cachedSourceTypeOptions();
        $groups = $this->cachedGroupsForFilter();

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
     * Stable (DO, item) key so rows are not split across groups when doc numbers
     * differ only by trimming or loose typing from the database driver.
     */
    private function normalizedDoItemPairKey(?string $sourceDocNum, $itemId): string
    {
        return trim((string) ($sourceDocNum ?? '')) . '|' . (int) ($itemId ?? 0);
    }

    /**
     * Match `source_doc_num` across legacy formatting (leading zeros, spaces, or
     * numeric storage) so DO line lists align with the ledger walk.
     */
    private function scopeTransactionsMatchingSourceDoc(Builder $query, string $doc): Builder
    {
        $doc = trim($doc);
        if ($doc === '') {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $inner) use ($doc) {
            $inner->where('transactions.source_doc_num', $doc)
                ->orWhereRaw('TRIM(transactions.source_doc_num) = ?', [$doc]);
            if (ctype_digit($doc)) {
                $inner->orWhereRaw('CAST(TRIM(transactions.source_doc_num) AS UNSIGNED) = ?', [(int) $doc]);
            }
        });
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
     * the Out column can aggregate all FIFO lines in that event. The Balance
     * column uses the **last contiguous run** of equal per-line quantities across
     * **all** DO Stock Out lines for that (DO, item): if that run has more than one
     * line, Balance is after the **first** line of that run; if it is a single line,
     * Balance is after that line. Earlier differing quantities only define where
     * that final run starts — they do not override it. Each (DO, item) pair is
     * evaluated independently. Only the latest event per (DO, item) is shown in the
     * log; older posting events are omitted.
     *
     * Cached per request (per filter scope) to avoid repeated scans.
     *
     * @return array{
     *     visible_ids: array<int,int>,
     *     event_qty: array<int,float>,
     *     is_latest: array<int,bool>,
     *     event_lines_by_rep_id: array<int, list<array{id:int, qty:float}>>,
     *     all_do_out_lines_by_latest_rep_id: array<int, list<array{id:int, qty:float}>>
     * }
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
        $eventLinesByRepId = [];
        $allDoOutLinesByLatestRepId = [];

        $byPair = $rows->groupBy(fn ($r) => $this->normalizedDoItemPairKey($r->source_doc_num, $r->item_id));

        foreach ($byPair as $pairRows) {
            $allOutLinesForPair = [];
            foreach ($pairRows as $row) {
                $isOut = in_array($row->source_type, $doSourceTypes, true)
                    && $row->transaction_type === 'Stock Out';
                if ($isOut) {
                    $allOutLinesForPair[] = [
                        'id' => (int) $row->id,
                        'qty' => abs((float) $row->transaction_qty),
                    ];
                }
            }

            $events = [];
            $current = null;

            foreach ($pairRows as $row) {
                $isOut = in_array($row->source_type, $doSourceTypes, true)
                    && $row->transaction_type === 'Stock Out';
                $isReversalIn = in_array($row->source_type, $reversalTypes, true)
                    && $row->transaction_type === 'Stock In';

                if ($isOut) {
                    if ($current === null) {
                        $current = ['ids' => [], 'qty' => 0.0, 'lines' => []];
                    }
                    $lineQty = abs((float) $row->transaction_qty);
                    $current['ids'][] = (int) $row->id;
                    $current['qty'] += $lineQty;
                    $current['lines'][] = ['id' => (int) $row->id, 'qty' => $lineQty];
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
                $eventLinesByRepId[$repId] = $ev['lines'] ?? [];
            }

            if ($eventCount > 0 && $allOutLinesForPair !== []) {
                $lastEv = $events[$eventCount - 1];
                $latestRepId = max($lastEv['ids']);
                $allDoOutLinesByLatestRepId[$latestRepId] = $allOutLinesForPair;
            }
        }

        return $this->doEventsCache[$cacheKey] = [
            'visible_ids' => $visibleIds,
            'event_qty' => $eventQty,
            'is_latest' => $isLatest,
            'event_lines_by_rep_id' => $eventLinesByRepId,
            'all_do_out_lines_by_latest_rep_id' => $allDoOutLinesByLatestRepId,
        ];
    }

    private function applyTransactionLogVisibilityRules(Builder $query): Builder
    {
        $doSourceTypes = $this->getDoSourceTypes();
        $reversalTypes = $this->getDoReversalSourceTypes();
        $events = $this->buildDoStockOutEvents();
        $visibleIds = $events['visible_ids'];
        $isLatest = $events['is_latest'];

        $currentDoRepresentativeIds = [];
        foreach ($visibleIds as $id) {
            $id = (int) $id;
            if (($isLatest[$id] ?? false) === true) {
                $currentDoRepresentativeIds[] = $id;
            }
        }

        // Hide reversal source types from the grid -- those are noise rows that
        // exist only to keep the ledger consistent. For DO Stock Out, show only
        // the representative row for the **latest** posting event per (DO, item);
        // older superseded reps are omitted from the log.
        return $query->where(function ($q) use ($reversalTypes) {
            $q->whereNotIn('transactions.source_type', $reversalTypes);
        })->where(function ($q) use ($doSourceTypes, $currentDoRepresentativeIds) {
            $q->whereNotIn('transactions.source_type', $doSourceTypes);
            if (!empty($currentDoRepresentativeIds)) {
                $q->orWhereIn('transactions.id', $currentDoRepresentativeIds);
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
     * Recompute the Balance column using the **full** ledger per item (including
     * hidden DO reversal rows) in **true posting order** (`created_at`, then `id`).
     *
     * The on-screen table is sorted by document date for readability, but balance
     * must follow the same sequence as inventory was actually posted; otherwise
     * reversals that share a DO’s document date appear “out of order” and the
     * running total no longer matches each row’s meaning (e.g. Out 2 with Balance 0).
     *
     * Opening balance per item = `qty_before` on that item’s earliest row (`MIN(id)`).
     * Each row’s displayed balance is on-hand **after** applying that row’s stored
     * `transaction_qty` (Stock In + / Stock Out −). This matches the DB ledger.
     *
     * The Out column for DO lines is still aggregated separately by
     * aggregateDoStockOutQuantities() on the paginated collection only.
     *
     * For each visible DO Stock Out row, Balance is then adjusted from **all** DO
     * Stock Out lines for that source doc + item (loaded directly from the DB so
     * rows are not missed when `source_doc_num` / `item_id` differ only by trimming
     * or typing). Lines are ordered by `id`; take the **last** stretch of consecutive
     * equal per-line quantities. If that stretch has multiple lines, use the on-hand
     * total after the **first** line in the stretch; if it is a single line, use the
     * total after that line.
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

        $allRows = Transaction::query()
            ->select('transactions.*')
            ->whereIn('transactions.item_id', $itemIds)
            ->orderBy('transactions.item_id', 'asc')
            ->orderBy('transactions.created_at', 'asc')
            ->orderBy('transactions.id', 'asc')
            ->get();

        if ($allRows->isEmpty()) {
            return [];
        }

        $bfRows = Transaction::query()
            ->whereIn('transactions.item_id', $itemIds)
            ->whereIn('transactions.id', function ($q) use ($itemIds) {
                $q->from('transactions')
                    ->selectRaw('MIN(transactions.id)')
                    ->whereIn('transactions.item_id', $itemIds)
                    ->groupBy('transactions.item_id');
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

            $balances[$iid] += $this->signedTransactionQtyForBalanceWalk($tx);
            $balanceByTxId[(int) $tx->id] = $balances[$iid];
        }

        $doSourceTypes = $this->getDoSourceTypes();
        $processedRepIds = [];

        foreach ($pageTransactions as $tx) {
            if (!in_array($tx->source_type, $doSourceTypes, true)
                || $tx->transaction_type !== 'Stock Out'
            ) {
                continue;
            }

            $repId = (int) $tx->id;
            if (isset($processedRepIds[$repId])) {
                continue;
            }
            $processedRepIds[$repId] = true;

            $doc = trim((string) ($tx->source_doc_num ?? ''));
            $itemId = (int) ($tx->item_id ?? 0);
            if ($doc === '' || $itemId === 0) {
                continue;
            }

            // Load every DO Stock Out line for this doc + item (same scopes as the
            // ledger walk). Do not rely only on the event-builder cache: grouping
            // used to split pairs when source_doc_num varied by whitespace/type.
            $lineQuery = $this->scopeTransactionsMatchingSourceDoc(
                Transaction::query()
                    ->where('transactions.item_id', $itemId)
                    ->whereIn('transactions.source_type', $doSourceTypes)
                    ->where('transactions.transaction_type', 'Stock Out'),
                $doc
            );

            $allOutLines = $lineQuery
                ->orderBy('transactions.id')
                ->get(['id', 'transaction_qty'])
                ->map(fn ($r) => [
                    'id' => (int) $r->id,
                    'qty' => abs((float) $r->transaction_qty),
                ])
                ->values()
                ->all();

            if ($allOutLines === []) {
                continue;
            }

            $balanceByTxId[$repId] = $this->doStockOutPairDisplayBalanceFromOutLines($balanceByTxId, $allOutLines);
        }

        return $balanceByTxId;
    }

    /**
     * Balance for the visible DO row from every Stock Out line on that (DO, item),
     * ordered by id: split into runs of consecutive equal qty; use the last run
     * (see recomputeDisplayBalances docblock).
     *
     * @param  array<int, float|int>  $balanceByTxId
     * @param  list<array{id:int, qty:float}>  $lines  ordered by id ascending
     */
    private function doStockOutPairDisplayBalanceFromOutLines(array $balanceByTxId, array $lines): float
    {
        if ($lines === []) {
            return 0.0;
        }

        $runs = [];
        $run = [$lines[0]];
        $n = count($lines);
        for ($i = 1; $i < $n; $i++) {
            $prevQty = (float) $run[count($run) - 1]['qty'];
            $curQty = (float) $lines[$i]['qty'];
            if (abs($prevQty - $curQty) < 1e-9) {
                $run[] = $lines[$i];
            } else {
                $runs[] = $run;
                $run = [$lines[$i]];
            }
        }
        $runs[] = $run;

        $lastRun = $runs[count($runs) - 1];
        $firstId = (int) $lastRun[0]['id'];
        $lastId = (int) $lastRun[count($lastRun) - 1]['id'];

        if (count($lastRun) === 1) {
            return (float) ($balanceByTxId[$lastId] ?? 0.0);
        }

        return (float) ($balanceByTxId[$firstId] ?? 0.0);
    }

    private function signedTransactionQtyForBalanceWalk(Transaction $tx): float
    {
        $qty = abs((float) $tx->transaction_qty);
        if ($tx->transaction_type === 'Stock In') {
            return $qty;
        }
        if ($tx->transaction_type === 'Stock Out') {
            return -$qty;
        }

        return 0.0;
    }

    /**
     * Distinct over `transactions` is expensive at scale; values change rarely.
     * Key includes default connection so multi-company (switchdb) never shares cache.
     */
    private function cachedSourceTypeOptions()
    {
        $key = 'transaction_log:source_types:' . config('database.default');

        return Cache::remember($key, 300, function () {
            return Transaction::distinct('source_type')->pluck('source_type');
        });
    }

    private function cachedGroupsForFilter()
    {
        $key = 'transaction_log:groups:' . config('database.default');

        return Cache::remember($key, 300, function () {
            return Group::orderBy('group_name')->get();
        });
    }
}
