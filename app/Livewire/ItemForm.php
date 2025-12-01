<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\WithPagination;
use App\Models\Category;
use App\Models\Family;
use App\Models\Group;
use App\Models\Item;
use App\Rules\UniqueInCurrentDatabase;
use Livewire\Attributes\Title;
use App\Models\Warehouse;
use App\Models\Location;
use App\Models\Supplier;
use App\Models\RestockList;
use App\Models\Transaction;
use App\Models\BatchTracking;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;


#[Title('UR | Manage Item')]
class ItemForm extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $isView = false;
    public $item = null;

    public $image;
    public $existing_image;
    public $imagePreview = null;
    public $isImageUploading = false;

    #[Validate('required', message: 'Category is required')]
    public $category = '';

    #[Validate('required', message: 'Family is required')]
    public $family = '';

    #[Validate('required', message: 'Group is required')]
    public $group = '';

    #[Validate('required', message: 'Supplier is required')]
    public $supplier = '';

    #[Validate('required', message: 'Warehouse is required')]
    public $warehouse = '';

    #[Validate('required', message: 'location is required')]
    public $location = '';

    public $categories;
    public $families;
    public $groups;
    public $suppliers;
    public $warehouses;
    public $locations = [];

    #[Validate('required', message: 'Item code is required')]
    public $item_code = '';

    #[Validate('required', message: 'Item name is required')]
    public $item_name = '';

    #[Validate('required|integer|min:0', message: 'Quantity must be a non-negative integer')]
    public $qty = 0;

    #[Validate('required|numeric|min:0', message: 'Customer price must be a non-negative number')]
    public $cust_price = 0;

    #[Validate('nullable|numeric|min:0', message: 'Cost must be a non-negative number')]
    public $cost = 0;

    #[Validate('required|numeric|min:0', message: 'Term price must be a non-negative number')]
    public $term_price = 0;

    #[Validate('required|numeric|min:0', message: 'Cash price must be a non-negative number')]
    public $cash_price = 0;

    #[Validate('nullable|integer|min:0', message: 'Stock alert level must be a non-negative integer')]
    public $stock_alert_level = 0;

    #[Validate('nullable|exists:suppliers,id', message: 'Invalid supplier')]
    public $sup_id = '';

    #[Validate('nullable|string', message: 'Invalid unit measurement')]
    public $um = '';

    #[Validate('nullable|string|max:255', message: 'Custom unit measurement must not exceed 255 characters')]
    public $custom_um = '';

    #[Validate('nullable|string|max:1000', message: 'Memo must be under 1000 characters')]
    public $memo = '';

    #[Validate('nullable|string|max:5000', message: 'Details must be under 5000 characters')]
    public $details = '';

    public $is_custom_um = false;
    public $activePageNumber = 1;

    public $batchTrackings = [];
    public $initialQuantity = 0;

    // New batch form fields
    public $newBatchQty;
    public $newBatchDate;
    public $newBatchSourceType = 'Manual Addition';
    public $newBatchSourceDoc = '-';
    public $showAddBatchSection = false;

    public function updatedImage()
    {
        $this->isImageUploading = true;
        $this->validate([
            'image' => 'nullable|image|max:2048',
        ]);

        if ($this->image) {
            $this->imagePreview = $this->image->temporaryUrl();
        }
        $this->isImageUploading = false;
    }
    
    public function updatedItemCode($value)
        {
            if ($this->item) {
                // Validate only if it's an existing item and the item code has changed
                if ($value !== $this->item->item_code) {
                    $this->validateOnly('item_code', [
                        'item_code' => ['required', new UniqueInCurrentDatabase('items', 'item_code', $this->item->id)],
                    ]);
                }
            } else {
                $this->validateOnly('item_code', [
                    'item_code' => ['required', new UniqueInCurrentDatabase('items', 'item_code')],
                ]);
            }
        }

        public function updatedItemName($value)
        {
            if ($this->item) {
                // Validate only if it's an existing item and the item code has changed
                if ($value !== $this->item->item_name) {
                    $this->validateOnly('item_name', [
                        'item_name' => ['required', new UniqueInCurrentDatabase('items', 'item_name', $this->item->id)],
                    ]);
                }
            } else {
                $this->validateOnly('item_name', [
                    'item_name' => ['required', new UniqueInCurrentDatabase('items', 'item_name')],
                ]);
            }
        }

    public function mount(Item $item)
    {
        $this->categories = Category::orderBy('cat_name')->get();
        $this->families = Family::orderBy('family_name')->get();
        $this->groups = Group::orderBy('group_name')->get();
        $this->warehouses = Warehouse::orderBy('warehouse_name')->get();
        $this->locations = Location::orderBy('location_name')->get();
        $this->suppliers = Supplier::orderBy('sup_name')->get();

        $this->isView = request()->routeIs('items.view');

        if ($item->id) {
            $this->item = $item;
            $this->item_code = $item->item_code;
            $this->item_name = $item->item_name;

            $this->cust_price = $item->cust_price;
            $this->cost = $item->cost;
            $this->term_price = $item->term_price;
            $this->cash_price = $item->cash_price;
            $this->stock_alert_level = $item->stock_alert_level;
            $this->supplier = $item->sup_id;
            $this->category = $item->cat_id;
            $this->family = $item->family_id;
            $this->group = $item->group_id;
            $this->warehouse = $item->warehouse_id;
            $this->location = $item->location_id;
            $this->existing_image = $item->image;
            $this->imagePreview = $this->existing_image ? Storage::url($this->existing_image) : null;
            $this->setUmValue($item->um);

            $this->memo = $item->memo;
            $this->details = $item->details;

            if ($this->warehouse) {
                $this->loadLocations(); 
            }

            $this->loadBatchTrackings();
        } else {
            $this->setUmValue('UNIT');
            $this->batchTrackings = [];
        }

        // Prefill new batch received date with today
        $this->newBatchDate = now()->format('Y-m-d');
    }

    public function loadBatchTrackings()
    {
        if ($this->item) {
            $this->batchTrackings = BatchTracking::where('item_id', $this->item->id)
                ->where('quantity', '>', 0)    
                ->with(['purchaseOrder', 'receivedBy'])
                ->orderBy('received_date', 'asc')
                ->get()
                ->map(function ($batch) {
                    return [
                        'id' => $batch->id,
                        'batch_num' => $batch->batch_num,
                        'quantity' => $batch->quantity,
                        'received_date' => $batch->received_date,
                        'po_num' => $batch->purchaseOrder->po_num ?? 'N/A',
                        'received_by' => $batch->receivedBy->name ?? 'N/A'
                    ];
                })
                ->toArray();
        }
    }

    /**
     * Add a new batch to the current item and create a transaction record.
     *
     * @param int $quantity
     * @param string|null $receivedDate  Y-m-d format (defaults to now())
     * @param string $sourceType         e.g. 'Manual Addition', 'Purchase Order'
     * @param string $sourceDocNum       e.g. PO number or '-'
     */
    public function addNewBatch($quantity, $receivedDate = null, $sourceType = 'Manual Addition', $sourceDocNum = '-')
    {
        if (!$this->item || !$this->item->id) {
            toastr()->error('Item must be saved before adding batches.');
            return;
        }

        if (!is_numeric($quantity) || $quantity <= 0) {
            toastr()->error('Quantity must be a positive number.');
            return;
        }

        try {
            $qtyBefore = BatchTracking::where('item_id', $this->item->id)->sum('quantity');

            $batch = BatchTracking::create([
                'batch_num' => $this->generateBatchNumber(),
                'po_id' => null,
                'item_id' => $this->item->id,
                'quantity' => (int) $quantity,
                'received_date' => $receivedDate ? \Carbon\Carbon::parse($receivedDate) : now(),
                'received_by' => Auth::id(),
            ]);

            $qtyAfter = BatchTracking::where('item_id', $this->item->id)->sum('quantity');

            Transaction::create([
                'item_id' => $this->item->id,
                'batch_id' => $batch->id,
                'user_id' => Auth::id(),
                'qty_on_hand' => $qtyAfter,
                'qty_before' => $qtyBefore,
                'qty_after' => $qtyAfter,
                'transaction_qty' => (int) $quantity,
                'transaction_type' => 'Stock In',
                'source_type' => $sourceType,
                'source_doc_num' => $sourceDocNum,
            ]);

            // Update item total quantity
            $this->item->update(['qty' => $qtyAfter]);

            // Refresh batches and alerts
            $this->loadBatchTrackings();
            $this->checkStockAlertLevel($this->item);

            toastr()->success('Batch added successfully.');
        } catch (\Exception $e) {
            toastr()->error('Failed to add batch: ' . $e->getMessage());
        }
    }

    /**
     * Wrapper for UI to add a new batch using bound inputs
     */
    public function submitAddBatch()
    {
        // Force fixed source fields regardless of UI
        $this->addNewBatch($this->newBatchQty, $this->newBatchDate, 'Manual Addition', '-');

        // Reset inputs on success path; errors are handled in addNewBatch
        $this->newBatchQty = null;
        $this->newBatchDate = now()->format('Y-m-d');
        $this->newBatchSourceType = 'Manual Addition';
        $this->newBatchSourceDoc = '-';
        $this->showAddBatchSection = false;
    }

    /**
     * Toggle the add batch section visibility
     */
    public function toggleAddBatchSection()
    {
        $this->showAddBatchSection = !$this->showAddBatchSection;
        if ($this->showAddBatchSection && empty($this->newBatchDate)) {
            $this->newBatchDate = now()->format('Y-m-d');
        }
    }

    public function updateBatchQuantity($batchId)
    {
        $batchIndex = array_search($batchId, array_column($this->batchTrackings, 'id'));
        if ($batchIndex === false) {
            return;
        }
    
        $newQuantity = $this->batchTrackings[$batchIndex]['quantity'];
    
        if ($newQuantity < 0) {
            toastr()->error('Quantity must be positive value');
            return;
        }
    
        $batch = BatchTracking::findOrFail($batchId);
        $oldQuantity = $batch->quantity;
    
        if ($oldQuantity === $newQuantity) {
            return;
        }

        // Get the current total quantity across all batches before update
        $qtyBefore = BatchTracking::where('item_id', $this->item->id)->sum('quantity');
        
        // Update the batch quantity
        $batch->update(['quantity' => $newQuantity]);
        
        // Calculate new total quantity after update across all batches
        $qtyAfter = BatchTracking::where('item_id', $this->item->id)->sum('quantity');
        
        // Record the transaction with correct batch quantities
        Transaction::create([
            'item_id' => $this->item->id,
            'batch_id' => $batchId,
            'user_id' => Auth::id(),
            'qty_on_hand' => $qtyAfter,
            'qty_before' => $qtyBefore,
            'qty_after' => $qtyAfter,
            'transaction_qty' => abs($newQuantity - $oldQuantity),
            'transaction_type' => $newQuantity > $oldQuantity ? 'Stock In' : 'Stock Out',
            'source_type' => 'Batch Adjustment',
            'source_doc_num' => $batch->batch_num
        ]);

        if ($newQuantity == 0) {
            array_splice($this->batchTrackings, $batchIndex, 1);
        }
    
        // Reload batch trackings and check stock level
        $this->loadBatchTrackings();
        $this->checkStockAlertLevel($this->item);
    
        // Update the total quantity in the items table
        $this->item->update(['qty' => $qtyAfter]);
    
        // Show success message
        toastr()->success('Batch quantity updated successfully and item total quantity recalculated');
    }


    public function setUmValue($value)
    {
        if (in_array($value, ['UNIT', 'BOX', 'KG', 'ROLL'])) {
            $this->um = $value;
            $this->is_custom_um = false;
            $this->custom_um = '';
        } elseif ($value === 'custom') {
            $this->um = 'custom';
            $this->is_custom_um = true;
            $this->custom_um = 'UNIT';
        } else {
            $this->um = 'custom';
            $this->is_custom_um = true;
            $this->custom_um = $value;
        }
    }
    
    public function updatedUm($value)
    {
        $this->setUmValue($value);
    }

    
    public function updatedWarehouse($value)
    {
        $this->location = '';
        $this->loadLocations();
    }

    public function loadLocations()
    {
        if ($this->warehouse) {
            $this->locations = Location::where('warehouse_id', $this->warehouse)->orderBy('location_name')->get();
        } else {
            $this->locations = collect();
        }
    }

    private function generateBatchNumber()
    {
        $date = now()->format('Ymd');
        $count = BatchTracking::whereDate('created_at', now())->count() + 1;
        return "BATCH-{$date}-" . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    public function addItem()
    {
        // Validate all properties
        $this->validate();
    
        $this->validate([
            'image' => 'nullable|image|max:2048',
        ]);
    
        $originalItemCode = $this->item ? $this->item->item_code : null;
        $originalItemName = $this->item ? $this->item->item_name : null;
    
        if ($this->item_code !== $originalItemCode) {
            $this->updatedItemCode($this->item_code);
        }
    
        if ($this->item_name !== $originalItemName) {
            $this->updatedItemName($this->item_name);
        }
    
        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }
    
        $unit_measurement = $this->um === 'custom' ? $this->custom_um : $this->um;
        $unit_measurement = $unit_measurement ?: 'UNIT';
    
        try {
            if ($this->item && !empty($this->batchTrackings)) {
                // Sort batch trackings by received_date
                usort($this->batchTrackings, function($a, $b) {
                    $batchA = BatchTracking::find($a['id']);
                    $batchB = BatchTracking::find($b['id']);
                    return $batchA->received_date <=> $batchB->received_date;
                });
        
                $baseTimestamp = now();
                
                foreach ($this->batchTrackings as $index => $batch) {
                    $batchRecord = BatchTracking::find($batch['id']);
                    
                    if ($batchRecord && $batchRecord->quantity !== $batch['quantity']) {
                        $oldQuantity = $batchRecord->quantity;
                        $newQuantity = $batch['quantity'];
        
                        if ($newQuantity >= 0) {
                            // Get total quantity across all batches before update
                            $qtyBeforeAll = BatchTracking::where('item_id', $this->item->id)->sum('quantity');
                            
                            // Update the batch quantity
                            $batchRecord->update(['quantity' => $newQuantity]);
                            
                            // Get total quantity across all batches after update
                            $qtyAfterAll = BatchTracking::where('item_id', $this->item->id)->sum('quantity');
        
                            // Create transaction with incremental timestamp and proper qty values
                            Transaction::create([
                                'item_id' => $this->item->id,
                                'batch_id' => $batch['id'],
                                'user_id' => Auth::id(),
                                'qty_on_hand' => $qtyAfterAll,
                                'qty_before' => $qtyBeforeAll,
                                'qty_after' => $qtyAfterAll,
                                'transaction_qty' => abs($newQuantity - $oldQuantity),
                                'transaction_type' => $newQuantity > $oldQuantity ? 'Stock In' : 'Stock Out',
                                'source_type' => 'Batch Adjustment',
                                'source_doc_num' => '-',
                                'created_at' => $baseTimestamp->copy()->addSeconds($index * 0.01),
                                'updated_at' => $baseTimestamp->copy()->addSeconds($index * 0.01)
                            ]);
                        }
                    }
                }
        
                // Update the item's total quantity
                $totalQuantity = BatchTracking::where('item_id', $this->item->id)->sum('quantity');
                $this->item->update([
                    'qty' => $totalQuantity
                ]);
            }
    
            $totalQuantity = $this->item ? 
                BatchTracking::where('item_id', $this->item->id)->sum('quantity') : 
                0;
    
            $itemData = [
                'item_code' => $this->item_code,
                'item_name' => $this->item_name,
                'qty' => $totalQuantity,
                'cust_price' => $this->cust_price,
                'cost' => $this->cost,
                'term_price' => $this->term_price,
                'cash_price' => $this->cash_price,
                'stock_alert_level' => $this->stock_alert_level,
                'sup_id' => $this->supplier,
                'um' => $unit_measurement,
                'cat_id' => $this->category,
                'family_id' => $this->family,
                'group_id' => $this->group,
                'warehouse_id' => $this->warehouse,
                'location_id' => $this->location,
                'memo' => $this->memo,
                'details' => $this->details,
            ];
    
            if ($this->image) {
                $imagePath = $this->image->store('items', 'public');
                $itemData['image'] = $imagePath;
                
                if ($this->item && $this->item->image) {
                    Storage::disk('public')->delete($this->item->image);
                }
            }
    
            if ($this->item) {
                $this->item->update($itemData);
                $updatedItem = $this->item->fresh();
                toastr()->success('Item updated successfully');
            } else {
                $updatedItem = Item::create($itemData);
                
                // Create initial batch for new items if they have initial quantity
                if ($this->initialQuantity > 0) {
                    $initialBatch = BatchTracking::create([
                        'batch_num' => $this->generateBatchNumber(),
                        'po_id' => null,
                        'item_id' => $updatedItem->id,
                        'quantity' => $this->initialQuantity,
                        'received_date' => now(),
                        'received_by' => Auth::id()
                    ]);

                    // Update item quantity to match batch
                    $updatedItem->update(['qty' => $this->initialQuantity]);

                    // Create transaction record
                    Transaction::create([
                        'item_id' => $updatedItem->id,
                        'batch_id' => $initialBatch->id,
                        'user_id' => Auth::id(),
                        'qty_on_hand' => $this->initialQuantity,
                        'qty_before' => 0,
                        'qty_after' => $this->initialQuantity,
                        'transaction_qty' => $this->initialQuantity,
                        'transaction_type' => 'Stock In',
                        'source_type' => 'Initial Stock',
                        'source_doc_num' => 'INITIAL'
                    ]);
                }
                
                toastr()->success('Item added successfully');
            }
            
            // Auto-apply any pending New Batch input when updating/adding the item
            // This ensures users don't have to press the "Add New Batch" button explicitly
            if (is_numeric($this->newBatchQty) && (float)$this->newBatchQty > 0) {
                // Ensure $this->item is set for addNewBatch to work
                $this->item = $updatedItem;
                $effectiveDate = $this->newBatchDate ?: now()->format('Y-m-d');
                $this->addNewBatch($this->newBatchQty, $effectiveDate, 'Manual Addition', '-');
                // Reset the pending new batch inputs
                $this->newBatchQty = null;
                $this->newBatchDate = now()->format('Y-m-d');
                $this->newBatchSourceType = 'Manual Addition';
                $this->newBatchSourceDoc = '-';
                $this->showAddBatchSection = false;
            }
            
            $this->checkStockAlertLevel($updatedItem);
            $this->loadBatchTrackings();
            
        } catch (\Exception $e) {
            toastr()->error('An error occurred: ' . $e->getMessage());
        }
    
        return $this->redirect('/items', navigate: true);
    }

    public function deleteImage() {
        if ($this->item && $this->item->image || $this->imagePreview) {
            if ($this->item && $this->item->image) {
                Storage::disk('public')->delete($this->item->image);
                $this->item->update(['image' => null]);
            }
            
            $this->existing_image = null;
            $this->imagePreview = null;
            $this->image = null;
            
            toastr()->success('Image deleted successfully');
        }
    }

    public function fetchItems()
    {
        return Item::paginate(50);
    }

    public function deleteItem(Item $item)
    {
        if ($item) {
            if ($item->restockLists()->exists()) {
                toastr()->error('This item cannot be deleted because it exists in restock list.');
                return;
            }

            if ($item->purchaseOrderItems()->exists()) {
                toastr()->error('This item cannot be deleted because it exists in a Purchase Order.');
                return;
            }

            if ($item->deliveryOrderItems()->exists()) {
                toastr()->error('This item cannot be deleted because it exists in a Delivery Order.');
                return;
            }

            try {
                $item->delete();
                toastr()->success('Item deleted successfully');
                return $this->redirect('/items', navigate: true);
            } catch (\Exception $e) {
                toastr()->error('An error occurred while deleting the item: ' . $e->getMessage());
            }
        }

        $items = $this->fetchItems();

        if ($items->isEmpty() && $this->activePageNumber > 1) {
            $this->gotoPage($this->activePageNumber - 1);
        } else {
            $this->gotoPage($this->activePageNumber);
        }
    }

    public function updatingPage($pageNumber)
    {
        $this->activePageNumber = $pageNumber;
    }

    public function render()
    {
        return view('livewire.item-form')->layout('layouts.app');
    }

    private function checkStockAlertLevel(Item $item)
    {
        if ($item->qty <= $item->stock_alert_level) {
            session()->flash('stock-alert', [
                'item_code' => $item->item_code,
                'item_name' => $item->item_name,
                'current_stock' => $item->qty,
                'alert_level' => $item->stock_alert_level
            ]);

            $existingRestockItem = RestockList::where('item_id', $item->id)->first();

            if (!$existingRestockItem) {
                $reorder_qty = 1;
            
                RestockList::create([
                    'item_id' => $item->id,
                    'remarks' => ''
                ]);
            }
        }
    }
}