<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\RestockList as RestockListModel;

#[Title('UR | Restock List')]
class RestockList extends Component
{
    use WithPagination;

    public $activePageNumber = 1;

    public $itemSearchTerm = '';
    public $selectedItems = [];
    public $stackedItems = []; // To hold selected items for display

    protected $listeners = ['deleteRestock'];

    public function render()
    {
        $restockItems = $this->fetchRestocks();
    
        // Update stacked items for display
        $this->stackedItems = RestockListModel::with('item')->whereIn('id', $this->selectedItems)->get();
    
        return view('livewire.restock-list', [
            'restockItems' => $restockItems,
            'selectedItemCount' => count($this->selectedItems),
            'stackedItems' => $this->stackedItems, // Pass stacked items to the view
        ])->layout('layouts.app');
    }
    
    // Add item to the stack
    public function toggleItemSelection($itemId)
    {
        if (in_array($itemId, $this->selectedItems)) {
            // Remove the item from the selection if it is already selected
            $this->selectedItems = array_diff($this->selectedItems, [$itemId]);
        } else {
            // Add the item to the selection
            $this->selectedItems[] = $itemId;
        }
    }
    
    public function fetchRestocks()
    {
        return RestockListModel::with('item')
            ->join('items', 'restock_lists.item_id', '=', 'items.id')
            ->where(function ($query) {
                $query->where('items.item_code', 'like', '%' . $this->itemSearchTerm . '%')
                      ->orWhere('items.item_name', 'like', '%' . $this->itemSearchTerm . '%');
            })
            ->whereNotIn('restock_lists.id', $this->selectedItems) // Exclude selected items here
            ->orderBy('restock_lists.created_at', 'desc')
            ->select('restock_lists.*')
            ->paginate(8);
    }

    public function deleteRestock(RestockListModel $restock)
    {
        if ($restock) {
            try {
                $restock->delete();
                toastr()->success('Item removed successfully');
            } catch (\Exception $e) {
                toastr()->error('An error occurred while deleting the item: ' . $e->getMessage());
            }
        }

        $restockItems = $this->fetchRestocks();

        // Handle pagination
        if($restockItems->isEmpty() && $this->activePageNumber > 1){
            $this->gotoPage($this->activePageNumber - 1);
        } else {
            $this->gotoPage($this->activePageNumber);
        }
    }

    public function navigateToAddPO()
    {
        // Store the selected item IDs in the session
        session(['stackedItems' => $this->selectedItems]);
    
        return $this->redirect('/purchase-orders/add', navigate: true);
    }
}
