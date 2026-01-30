<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Item;
use App\Models\Group;
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
            'selectedGroupId'
        ]);
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

        // Return the view for rendering
        return view('livewire.transaction-log', [
            'transactions' => $transactions,
            'filteredItem' => $filteredItem,
            'sourceTypeOptions' => $sourceTypeOptions,
            'groups' => $groups,
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

        return view('livewire.transaction-log', [
            'transactions' => $paginator,
            'filteredItem' => $filteredItem,
            'sourceTypeOptions' => $sourceTypeOptions,
            'groups' => $groups,
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
