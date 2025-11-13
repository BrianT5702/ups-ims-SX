<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\StockMovement;
use App\Models\StockMovementItem;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;

#[Title('UR | Stock Movement')]
#[Layout('layouts.app')]
class StockMovementForm extends Component
{
    public $isView = false;
    public $stockMovement = null;
    public $stackedItems = [];
    public $movement_type = '';
    public $movement_date;
    public $reference_no;
    public $remarks;
    public $itemSearchTerm = '';
    public $itemSearchResults = [];

    public function mount(StockMovement $stockMovement)
    {
        $this->isView = request()->routeIs('stock-movements.view');
        
        if ($stockMovement->id) {
            // Load the relationships
            $stockMovement->load(['items.item']);
            $this->stockMovement = $stockMovement;
            $this->movement_type = $stockMovement->movement_type;
            $this->movement_date = $stockMovement->movement_date->format('Y-m-d\TH:i');
            $this->reference_no = $stockMovement->reference_no;
            $this->remarks = $stockMovement->remarks;

            // Load stock movement items
            foreach ($stockMovement->items as $movementItem) {
                $this->stackedItems[] = [
                    'item' => [
                        'id' => $movementItem->item->id,
                        'item_code' => $movementItem->item->item_code,
                        'item_name' => $movementItem->item->item_name,
                        'qty' => $movementItem->item->qty,
                    ],
                    'quantity' => $movementItem->quantity,
                    'remarks' => $movementItem->remarks,
                ];
            }
        } else {
            $this->movement_date = now()->format('Y-m-d\TH:i');
        }
    }

    public function searchItems()
    {
        if (strlen($this->itemSearchTerm) >= 2) {
            $this->itemSearchResults = Item::where('item_code', 'like', '%' . $this->itemSearchTerm . '%')
                ->orWhere('item_name', 'like', '%' . $this->itemSearchTerm . '%')
                ->limit(10)
                ->get();
        } else {
            $this->itemSearchResults = [];
        }
    }

    public function addItem($itemId)
    {
        $item = Item::find($itemId);
        
        if ($item) {
            // Check if item already exists in stacked items
            $existingItemIndex = collect($this->stackedItems)->search(function ($stackedItem) use ($itemId) {
                return $stackedItem['item']['id'] == $itemId;
            });

            if ($existingItemIndex !== false) {
                // Item already exists, you might want to show a message or increment quantity
                return;
            }

            $this->stackedItems[] = [
                'item' => [
                    'id' => $item->id,
                    'item_code' => $item->item_code,
                    'item_name' => $item->item_name,
                    'qty' => $item->qty,
                ],
                'quantity' => 1,
                'remarks' => '',
            ];

            $this->itemSearchTerm = '';
            $this->itemSearchResults = [];
        }
    }

    public function removeItem($index)
    {
        if (isset($this->stackedItems[$index])) {
            unset($this->stackedItems[$index]);
            $this->stackedItems = array_values($this->stackedItems);
        }
    }

    public function saveStockMovement()
    {
        $this->validate([
            'movement_type' => 'required|in:In,Out',
            'movement_date' => 'required|date',
            'stackedItems' => 'required|array|min:1',
            'stackedItems.*.quantity' => 'required|integer|min:1',
            'stackedItems.*.item.id' => 'required|exists:items,id',
        ]);

        try {
            DB::beginTransaction();

            if ($this->stockMovement && $this->stockMovement->id) {
                // Update existing stock movement
                $this->stockMovement->update([
                    'movement_type' => $this->movement_type,
                    'movement_date' => $this->movement_date,
                    'reference_no' => $this->reference_no,
                    'remarks' => $this->remarks,
                ]);

                // Delete existing items and recreate them
                $this->stockMovement->items()->delete();

                foreach ($this->stackedItems as $item) {
                    StockMovementItem::create([
                        'stock_movement_id' => $this->stockMovement->id,
                        'item_id' => $item['item']['id'],
                        'quantity' => $item['quantity'],
                        'remarks' => $item['remarks'],
                    ]);
                }

                $message = 'Stock movement updated successfully.';
            } else {
                // Create new stock movement
                $stockMovement = StockMovement::create([
                    'movement_type' => $this->movement_type,
                    'movement_date' => $this->movement_date,
                    'user_id' => Auth::id(),
                    'reference_no' => $this->reference_no,
                    'remarks' => $this->remarks,
                ]);

                foreach ($this->stackedItems as $item) {
                    StockMovementItem::create([
                        'stock_movement_id' => $stockMovement->id,
                        'item_id' => $item['item']['id'],
                        'quantity' => $item['quantity'],
                        'remarks' => $item['remarks'],
                    ]);
                }

                $message = 'Stock movement created successfully.';
            }

            DB::commit();

            toastr()->success($message);
            return redirect()->route('stock-movements');

        } catch (\Exception $e) {
            DB::rollback();
            toastr()->error('Error saving stock movement: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.stock-movement-form');
    }
}
