<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\Customer;
use App\Models\Item;
use App\Models\User;
use App\Models\Transaction;
use App\Rules\UniqueInCurrentDatabase;
use App\Rules\ExistsInCurrentDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use App\Models\RestockList;
use App\Models\BatchTracking;
use App\Models\CustomerSnapshot;

#[Title('UR | Manage Delivery Order')]
class DOForm extends Component
{
    public $isView = false;
    public $deliveryOrder = null;
    public $stackedItems = [];
    public $user_id;
    public $cust_id;
    public $selectedCustomer = null;

    public $do_num = null;
    public $ref_num;
    public $date = null;
    public $remark;
    public $salesmanId;
    public $salesman_id;
    public $cust_po;
    public $total_price_line_item;
    public $total_amount = 0;
    public $itemSearchTerm = '';
    public $itemSearchResults = [];
    public $itemHighlightIndex = -1;
    public $customerSearchTerm = '';
    public $customerSearchResults = [];
    public $salesmanSearchTerm = '';
    public $salesmanSearchResults = [];
    public $salesmen = [];
    public $saveAsDraft = false;
    public $status = 'Save to Draft';
    private bool $isPreviewMode = false;

    public function mount(DeliveryOrder $deliveryOrder)
    {

        $this->isView = request()->routeIs('delivery-orders.view');

        
        if ($deliveryOrder) {
            $this->deliveryOrder = $deliveryOrder;
            $this->do_num = $deliveryOrder->do_num;
            $this->ref_num = $deliveryOrder->ref_num;
            $this->date = $deliveryOrder->date;
            $this->cust_id = $deliveryOrder->cust_id;
            $this->selectedCustomer = $deliveryOrder->customer;
            $this->salesman_id = $deliveryOrder->salesman_id;
            $this->cust_po = $deliveryOrder->cust_po;
            $this->remark = $deliveryOrder->remark;
            $this->total_amount = $deliveryOrder->total_amount;
    
            // Clear previous stackedItems
            $this->stackedItems = [];
    
            // Load delivery order items
            foreach ($deliveryOrder->items as $doItem) {
                // Determine if the price was manually modified by comparing with tier prices
                $tierPrices = [
                    'Customer Price' => $doItem->item->cust_price,
                    'Term Price' => $doItem->item->term_price,
                    'Cash Price' => $doItem->item->cash_price,
                ];
                
                $pricingTier = $doItem->pricing_tier ?? '';
                $priceManuallyModified = false;
                
                // If pricing tier is set, check if price matches the tier price
                if ($pricingTier && in_array($pricingTier, ['Customer Price', 'Term Price', 'Cash Price'])) {
                    $expectedPrice = match ($pricingTier) {
                        'Customer Price' => $doItem->item->cust_price,
                        'Term Price' => $doItem->item->term_price,
                        'Cash Price' => $doItem->item->cash_price,
                        default => $doItem->item->cust_price
                    };
                    $priceManuallyModified = ($doItem->unit_price != $expectedPrice);
                } else {
                    // No pricing tier means custom price
                    $priceManuallyModified = true;
                }
                
                $this->stackedItems[] = [
                    'item' => [
                        'id' => $doItem->item->id,
                        'item_code' => $doItem->item->item_code,
                        'item_name' => $doItem->item->item_name,
                        'qty' => $doItem->item->qty, // Current inventory quantity
                        'cost' => $doItem->item->cost,
                        'cust_price' => $doItem->item->cust_price,
                        'term_price' => $doItem->item->term_price,
                        'cash_price' => $doItem->item->cash_price,
                        'latest_do_price' => $this->getLatestDOPriceForItem($doItem->item->id, $this->cust_id),
                        'latest_do_date' => $this->getLatestDODateForItem($doItem->item->id, $this->cust_id),
                    ],
                    'item_qty' => $doItem->qty, // Quantity in this specific delivery order
                    'pricing_tier' => $pricingTier, // Load the saved pricing tier
                    'item_unit_price' => $doItem->unit_price,
                    'amount' => $doItem->amount,
                    'more_description' => $doItem->more_description,
                    'custom_item_name' => $doItem->custom_item_name ?? $doItem->item->item_name,
                    'price_manually_modified' => $priceManuallyModified,
                ];
            }
    
            // Set search terms for customer and salesman
            if ($deliveryOrder->customer) {
                $this->customerSearchTerm = $deliveryOrder->customer->cust_name;
            }
    
            if ($deliveryOrder->salesman) {
                $this->salesmanSearchTerm = $deliveryOrder->salesman->name;
            }
            // Allow editing even if status is Completed
        } 
    

    }
    








    public function updatedCustomerSearchTerm()
    {
        if (!$this->isView) {
            $this->searchCustomers();
        }
    }

    public function searchCustomers()
    {
        if (!empty($this->customerSearchTerm)) {
            $this->customerSearchResults = Customer::where('cust_name', 'like', '%' . $this->customerSearchTerm . '%')
                ->orWhere('account', 'like', '%' . $this->customerSearchTerm . '%')
                ->orderBy('cust_name','asc')
                ->limit(10)
                ->get();
        } else {
            $this->customerSearchResults = [];
        }
    }

    public function selectCustomer($custId)
    {
        if (!$this->isView) {
            $this->selectedCustomer = Customer::find($custId);
            $this->cust_id = $custId; 
            $this->customerSearchTerm = $this->selectedCustomer->cust_name;
            $this->customerSearchResults = [];
            
            // Debug: Log the customer currency
            \Log::info('Selected customer currency: ' . ($this->selectedCustomer->currency ?? 'null'));

            // Default salesman to customer's assigned salesman if present
            if ($this->selectedCustomer && $this->selectedCustomer->salesman_id) {
                $this->salesman_id = $this->selectedCustomer->salesman_id;
                $this->salesmanSearchTerm = optional(User::find($this->salesman_id))->name;
            }

            // Refresh latest DO prices per item for this customer
            foreach ($this->stackedItems as $key => $stackedItem) {
                $this->stackedItems[$key]['item']['latest_do_price'] = $this->getLatestDOPriceForItem($stackedItem['item']['id'], $this->cust_id);
                $this->stackedItems[$key]['item']['latest_do_date'] = $this->getLatestDODateForItem($stackedItem['item']['id'], $this->cust_id);
                // If current selection is Previous Price, update the unit price as well
                if (($stackedItem['pricing_tier'] ?? '') === 'Previous Price') {
                    $this->stackedItems[$key]['item_unit_price'] = $this->stackedItems[$key]['item']['latest_do_price'] ?? 0;
                    $this->stackedItems[$key]['amount'] = $this->stackedItems[$key]['item_qty'] * $this->stackedItems[$key]['item_unit_price'];
                }
            }
            $this->calculateTotalAmount();
        }
    }

    public function updatedSalesmanSearchTerm()
    {
        // Deprecated search; using dropdown list
    }

    public function searchSalesman()
    {
        // Deprecated; using dropdown list
        $this->salesmanSearchResults = [];
    }
    
    public function selectSalesman($salesmanId)
    {
        if (!$this->isView) {
            $this->selectedSalesman = User::find($salesmanId);
    
            if ($this->selectedSalesman && $this->selectedSalesman->hasRole('Salesperson')) {
                $this->salesman_id = $salesmanId;
                $this->salesmanSearchTerm = $this->selectedSalesman->name;
                $this->salesmanSearchResults = [];
            }
        }
    }

    public function updatedItemSearchTerm()
    {
        if (!$this->isView) {
            $this->searchItems();
            // reset highlight on new term
            $this->itemHighlightIndex = (count($this->itemSearchResults) > 0) ? 0 : -1;
        }
    }

    public function searchItems()
    {
        if (!empty($this->itemSearchTerm)) {
            $isNewDO = !$this->deliveryOrder || !$this->deliveryOrder->id;
            $isDraftOrPreview = $this->saveAsDraft || $this->isPreviewMode || 
                              ($this->deliveryOrder && $this->deliveryOrder->status === 'Save to Draft');
            
            $query = Item::where('item_code', 'like', '%' . $this->itemSearchTerm . '%')
                ->orWhere('item_name', 'like', '%' . $this->itemSearchTerm . '%');
            
            // Only filter out zero quantity items for existing completed DOs
            // Show all items for new DOs, drafts, and previews
            if (!$isNewDO && !$isDraftOrPreview) {
                $query->where('qty', '>', 0);
            }
            
            $this->itemSearchResults = $query->orderBy('item_name','asc')
                ->limit(50)
                ->get();
            $this->itemHighlightIndex = (count($this->itemSearchResults) > 0) ? 0 : -1;
        } else {
            $this->itemSearchResults = [];
            $this->itemHighlightIndex = -1;
        }
    }

    public function moveItemHighlight($delta)
    {
        $count = count($this->itemSearchResults);
        if ($count === 0) { $this->itemHighlightIndex = -1; return; }
        $new = $this->itemHighlightIndex + (int)$delta;
        if ($new < 0) { $new = $count - 1; }
        if ($new >= $count) { $new = 0; }
        $this->itemHighlightIndex = $new;
    }

    public function addHighlightedItem()
    {
        $count = count($this->itemSearchResults);
        if ($count === 0 || $this->itemHighlightIndex < 0 || $this->itemHighlightIndex >= $count) { return; }
        $item = $this->itemSearchResults[$this->itemHighlightIndex];
        $this->addItem($item->id);
    }

    public function addItem($itemId)
    {
        if (!$this->isView) {
            $item = Item::find($itemId);

            // Enhanced validation: Check if item exists and has sufficient stock
            if (!$item) {
                toastr()->error('Item not found.');
                return;
            }

            if ($item->qty <= 0) {
                toastr()->warning('Stock level is 0. Unable to add this item to the DO.');
                return;
            }

            $itemExists = false;

            foreach ($this->stackedItems as $key => $stackedItem) {
                if ($stackedItem['item']['id'] === $item->id) {
                    // Only enforce stock limits for completed DOs, allow exceed for new DOs, drafts, and previews
                    $currentQty = $this->stackedItems[$key]['item_qty'];
                    $availableStock = $item->qty;
                    $isNewDO = !$this->deliveryOrder || !$this->deliveryOrder->id;
                    $isDraftOrPreview = $this->saveAsDraft || $this->isPreviewMode || 
                                      ($this->deliveryOrder && $this->deliveryOrder->status === 'Save to Draft');
                    
                    // Only enforce stock limits for existing completed DOs
                    if (!$isNewDO && !$isDraftOrPreview && $currentQty >= $availableStock) {
                        toastr()->warning("Cannot add more of this item. Available stock: {$availableStock}, Current quantity: {$currentQty}");
                        return;
                    }
                    
                    $this->stackedItems[$key]['item_qty'] += 1;
                    $this->stackedItems[$key]['amount'] = 
                        $this->stackedItems[$key]['item_qty'] * $this->stackedItems[$key]['item_unit_price'];
                    $itemExists = true;
                    break;
                }
            }

            if (!$itemExists) {
                // Check if maximum items limit (15) is reached only for new items
                if (count($this->stackedItems) >= 15) {
                    toastr()->error('Maximum of 15 items allowed per delivery order. Please remove some items before adding new ones.');
                    return;
                }

                $this->stackedItems[] = [
                    'item' => [
                        'id' => $item->id,
                        'item_code' => $item->item_code,
                        'item_name' => $item->item_name,
                        'qty' => $item->qty,
                        'cost' => $item->cost,
                        'cust_price' => $item->cust_price,
                        'term_price' => $item->term_price,
                        'cash_price' => $item->cash_price,
                        'latest_do_price' => $this->getLatestDOPriceForItem($item->id, $this->cust_id),
                        'latest_do_date' => $this->getLatestDODateForItem($item->id, $this->cust_id),
                    ],
                    'item_qty' => 1,
                    'pricing_tier' => '',
                    'item_unit_price' => 0,
                    'amount' => 0,
                    'total_amount' => 0,
                    'more_description' => null,
                    'custom_item_name' => $item->item_name,
                    'price_manually_modified' => true
                ];
            }

            $this->itemSearchTerm = '';
            $this->itemSearchResults = [];

            $this->calculateTotalAmount();
        }
    }

    public function removeItem($index)
    {
        if (!$this->isView) {
            unset($this->stackedItems[$index]);
            $this->stackedItems = array_values($this->stackedItems);
        }
    }

    public function updateItemPricing($index)
    {
        if (isset($this->stackedItems[$index])) {
            $pricingTier = $this->stackedItems[$index]['pricing_tier'] ?? '';
            $item = $this->stackedItems[$index]['item'];
            
            if ($pricingTier && in_array($pricingTier, ['Customer Price', 'Term Price', 'Cash Price', 'Cost', 'Previous Price'])) {
                $tierPrice = match ($pricingTier) {
                    'Customer Price' => $item['cust_price'],
                    'Term Price' => $item['term_price'],
                    'Cash Price' => $item['cash_price'],
                    'Cost' => $item['cost'],
                    'Previous Price' => $item['latest_do_price'] ?? $this->getLatestDOPriceForItem($item['id'], $this->cust_id),
                    default => $item['cust_price']
                };
                $tierPrice = floatval($tierPrice ?? 0);
                
                // Always update the price when a pricing tier is selected
                $this->stackedItems[$index]['item_unit_price'] = $tierPrice;
                // Clear the manually modified flag since we're using tier pricing
                $this->stackedItems[$index]['price_manually_modified'] = false;
            } elseif (empty($pricingTier)) {
                // If pricing tier is empty (Custom Price), default unit price to 0 and mark as manual
                $this->stackedItems[$index]['price_manually_modified'] = true;
                $this->stackedItems[$index]['item_unit_price'] = 0;
            }
            
            // Always recalculate amount based on current unit price
            $this->stackedItems[$index]['amount'] = intval($this->stackedItems[$index]['item_qty'] ?? 0) * floatval($this->stackedItems[$index]['item_unit_price'] ?? 0);
            
            $this->calculateTotalAmount();
        }
    }

    public function updateItemUnitPrices()
    {
        foreach ($this->stackedItems as $key => $stackedItem) {
            $pricingTier = $stackedItem['pricing_tier'] ?? '';
            
            if ($pricingTier && in_array($pricingTier, ['Customer Price', 'Term Price', 'Cash Price', 'Cost', 'Previous Price'])) {
                $tierPrice = match ($pricingTier) {
                    'Customer Price' => $stackedItem['item']['cust_price'],
                    'Term Price' => $stackedItem['item']['term_price'],
                    'Cash Price' => $stackedItem['item']['cash_price'],
                    'Cost' => $stackedItem['item']['cost'],
                    'Previous Price' => $stackedItem['item']['latest_do_price'] ?? $this->getLatestDOPriceForItem($stackedItem['item']['id'], $this->cust_id),
                    default => $stackedItem['item']['cust_price']
                };
                $tierPrice = floatval($tierPrice ?? 0);
                
                // Always update the price for pricing tier items
                $this->stackedItems[$key]['item_unit_price'] = $tierPrice;
                
                // Always recalculate amount based on current unit price
                $this->stackedItems[$key]['amount'] = intval($this->stackedItems[$key]['item_qty'] ?? 0) * floatval($this->stackedItems[$key]['item_unit_price'] ?? 0);
            } else {
                // For custom prices, just recalculate amount without changing the price
                $this->stackedItems[$key]['amount'] = $this->stackedItems[$key]['item_qty'] * $this->stackedItems[$key]['item_unit_price'];
            }
        }
    }

    public function selectPricingTier($index, $tier)
    {
        if (!isset($this->stackedItems[$index])) {
            return;
        }

        $this->stackedItems[$index]['pricing_tier'] = $tier ?: '';
        if ($this->stackedItems[$index]['pricing_tier'] === '') {
            // Immediately set to 0 for custom price and recalc
            $this->stackedItems[$index]['item_unit_price'] = 0;
            $this->stackedItems[$index]['price_manually_modified'] = true;
            $this->updatePriceLine($index);
        } else {
            $this->updateItemPricing($index);
        }
    }

    public function updatePriceLine($index)
    {
        if (isset($this->stackedItems[$index])) {
            $item = $this->stackedItems[$index];
            $requestedQty = intval($item['item_qty'] ?? 0);
            $availableStock = $item['item']['qty'] ?? 0;
            $isNewDO = !$this->deliveryOrder || !$this->deliveryOrder->id;
            $isDraftOrPreview = $this->saveAsDraft || $this->isPreviewMode || 
                              ($this->deliveryOrder && $this->deliveryOrder->status === 'Save to Draft');
            
            // Only validate stock limits for existing completed DOs, allow exceed for new DOs, drafts, and previews
            if (!$isNewDO && !$isDraftOrPreview && $requestedQty > $availableStock) {
                toastr()->warning("Cannot set quantity to {$requestedQty}. Available stock: {$availableStock}");
                // Reset to available stock or current quantity, whichever is lower
                $this->stackedItems[$index]['item_qty'] = min($availableStock, $this->stackedItems[$index]['item_qty'] ?? 1);
                $requestedQty = $this->stackedItems[$index]['item_qty'];
            }
            
            $item['item_qty'] = $requestedQty;
            $item['item_unit_price'] = floatval($item['item_unit_price'] ?? 0);
            $this->stackedItems[$index]['amount'] = $item['item_qty'] * $item['item_unit_price'];

            $this->calculateTotalAmount();
        }
    }

    public function updatedStackedItems($value, $key)
    {
        // This method is called when any stackedItems property is updated
        if (str_contains($key, '.item_qty')) {
            $index = explode('.', $key)[1];
            $this->updatePriceLine($index);
        }
    }

    public function updateUnitPrice($index)
    {
        if (isset($this->stackedItems[$index])) {
            $currentTier = $this->stackedItems[$index]['pricing_tier'] ?? '';
            // Only treat as manual/custom if no tier is selected
            if ($currentTier === '' || $currentTier === null) {
                $this->stackedItems[$index]['price_manually_modified'] = true;
                $this->updatePriceLine($index);
            }
        }
    }  

    public function calculateTotalAmount()
    {
        $this->total_amount = 0;

        foreach ($this->stackedItems as $stackedItem) {
            $this->total_amount += $stackedItem['amount'];
        }
    }

    public function addDO()
    {
        if ($this->isView) {
            return;
        }

        // Posting should finalize unless explicitly saving draft

        // Custom validation for pricing tier - only required if not manually modified
        $this->validate([
            'do_num' => ['required', new UniqueInCurrentDatabase('delivery_orders', 'do_num', $this->deliveryOrder?->id)],
            'cust_id' => ['required', new ExistsInCurrentDatabase('customers', 'id')],
            'salesman_id' => ['required', new ExistsInCurrentDatabase('users', 'id')],
            'date' => 'required|date',
            'cust_po' => 'required',
            'stackedItems.*.item_qty' => 'required|integer|min:1',
            'stackedItems.*.item_unit_price' => 'required|numeric|min:0',
        ], [
            'do_num.required' => 'The DO number field is required.',
            'do_num.unique' => 'The DO number is already taken.',
            'cust_id.required' => 'The customer field is required.',
            'cust_id.exists' => 'The selected customer does not exist.',
            'salesman_id.required' => 'The salesperson field is required.',
            'salesman_id.exists' => 'The selected salesperson does not exist.',
            'date.required' => 'The date field is required.',
            'date.date' => 'The date must be a valid date.',
            'cust_po.required' => 'The customer PO number is required.',
            'stackedItems.*.item_qty.required' => 'The item quantity is required for each item.',
            'stackedItems.*.item_qty.integer' => 'The item quantity must be an integer.',
            'stackedItems.*.item_qty.min' => 'The item quantity must be at least 1.',
            'stackedItems.*.item_unit_price.required' => 'The unit price is required for each item.',
            'stackedItems.*.item_unit_price.numeric' => 'The unit price must be a number.',
            'stackedItems.*.item_unit_price.min' => 'The unit price must be at least 0.',
        ]);

        // Custom validation for pricing tier - only required if price hasn't been manually modified
        foreach ($this->stackedItems as $index => $item) {
            if (!isset($item['price_manually_modified']) || !$item['price_manually_modified']) {
                if (empty($item['pricing_tier']) || !in_array($item['pricing_tier'], ['Customer Price', 'Term Price', 'Cash Price', 'Cost', 'Previous Price'])) {
                    $this->addError("stackedItems.{$index}.pricing_tier", 'The pricing tier is required when using standard pricing.');
                }
            }
        }

        // Final validation: Check that no item quantities exceed available stock (only for completed DOs)
        $isNewDO = !$this->deliveryOrder || !$this->deliveryOrder->id;
        $isDraftOrPreview = $this->saveAsDraft || $this->isPreviewMode || 
                          ($this->deliveryOrder && $this->deliveryOrder->status === 'Save to Draft');
        
        // Only enforce stock limits for existing completed DOs, allow exceed for new DOs, drafts, and previews
        if (!$isNewDO && !$isDraftOrPreview) {
            foreach ($this->stackedItems as $index => $item) {
                $requestedQty = intval($item['item_qty'] ?? 0);
                $availableStock = $item['item']['qty'] ?? 0;
                
                if ($requestedQty > $availableStock) {
                    $this->addError("stackedItems.{$index}.item_qty", "Quantity ({$requestedQty}) exceeds available stock ({$availableStock}) for item {$item['item']['item_code']}");
                }
            }
        }

        // Check if there are any validation errors
        if ($this->getErrorBag()->any()) {
            return;
        }

        try {
            DB::beginTransaction();
            
            $this->updateItemUnitPrices();
            $this->calculateTotalAmount();

            $this->updateItemUnitPrices();
            $this->calculateTotalAmount();

            // Determine draft mode strictly from explicit Save Draft action
            $isDraft = ($this->saveAsDraft === true);
            
            // Track if this is a new delivery order
            $isNewDeliveryOrder = !($this->deliveryOrder && $this->deliveryOrder->id);

            if ($this->deliveryOrder && $this->deliveryOrder->id) {
                // Reconcile stock by applying only the delta between previous DO items and current form
                $previousQtyByItem = DeliveryOrderItem::where('do_id', $this->deliveryOrder->id)
                    ->get()
                    ->groupBy('item_id')
                    ->map(fn($group) => $group->sum('qty'))
                    ->toArray();

                $newQtyByItem = collect($this->stackedItems)
                    ->mapWithKeys(function($item) {
                        return [$item['item']['id'] => (int) ($item['item_qty'] ?? 0)];
                    })
                    ->toArray();

                // Only process stock changes if status actually changed
                $previousStatus = $this->deliveryOrder->status;
                $newStatus = $isDraft ? 'Save to Draft' : 'Completed';
                $statusChanged = $previousStatus !== $newStatus;
                
                if ($statusChanged) {
                    if ($previousStatus === 'Completed' && $newStatus === 'Save to Draft') {
                        // Special case: Restore all stock when changing from Completed to Draft
                        foreach ($this->stackedItems as $item) {
                            $itemId = $item['item']['id'];
                            $qty = (int) ($item['item_qty'] ?? 0);
                            
                            if ($qty > 0) {
                                $this->restoreToBatchesFifo($itemId, $qty);
                                
                                // Update item qty to reflect current batches
                                $itemRecord = Item::find($itemId);
                                if ($itemRecord) {
                                    $itemRecord->qty = BatchTracking::where('item_id', $itemId)->sum('quantity');
                                    $itemRecord->save();
                                    $this->checkStockAlertLevel($itemRecord);
                                }
                            }
                        }
                    } elseif ($previousStatus === 'Save to Draft' && $newStatus === 'Completed') {
                        // Validate stock availability before deducting
                        foreach ($this->stackedItems as $item) {
                            $itemId = $item['item']['id'];
                            $qty = (int) ($item['item_qty'] ?? 0);
                            $availableStock = $item['item']['qty'] ?? 0;
                            
                            if ($qty > 0 && $qty > $availableStock) {
                                throw new \Exception("Insufficient stock for item {$item['item']['item_code']}. Available: {$availableStock}, Requested: {$qty}");
                            }
                        }
                        
                        // Special case: Deduct stock when changing from Draft to Completed
                        foreach ($this->stackedItems as $item) {
                            $itemId = $item['item']['id'];
                            $qty = (int) ($item['item_qty'] ?? 0);
                            
                            if ($qty > 0) {
                                $this->deductFromBatchesFifo($itemId, $qty, false);
                                
                                // Update item qty to reflect current batches
                                $itemRecord = Item::find($itemId);
                                if ($itemRecord) {
                                    $itemRecord->qty = BatchTracking::where('item_id', $itemId)->sum('quantity');
                                    $itemRecord->save();
                                    $this->checkStockAlertLevel($itemRecord);
                                }
                            }
                        }
                    } else {
                        // Use delta logic for other status changes
                        $this->reconcileDoStockDeltas($previousQtyByItem, $newQtyByItem, $isDraft);
                    }
                }
                // Create new customer snapshot
                $customer = Customer::find($this->cust_id);
                $customerSnapshot = CustomerSnapshot::create([
                    'customer_id' => $customer->id,
                    'account' => $customer->account,
                    'cust_name' => $customer->cust_name,
                    'address_line1' => $customer->address_line1,
                    'address_line2' => $customer->address_line2,
                    'address_line3' => $customer->address_line3,
                    'address_line4' => $customer->address_line4,
                    'phone_num' => $customer->phone_num,
                    'fax_num' => $customer->fax_num,
                    'email' => $customer->email,
                    'area' => $customer->area,
                    'term' => $customer->term,
                    'business_registration_no' => $customer->business_registration_no,
                    'gst_registration_no' => $customer->gst_registration_no,
                    'pricing_tier' => $customer->pricing_tier,
                    'currency' => $customer->currency,
                ]);

                // Update existing delivery order's basic information
                $this->deliveryOrder->do_num = $this->do_num;
                $this->deliveryOrder->ref_num = $this->ref_num;
                $this->deliveryOrder->date = $this->date;
                $this->deliveryOrder->cust_id = $this->cust_id;
                $this->deliveryOrder->user_id = $this->user_id;
                $this->deliveryOrder->salesman_id = $this->salesman_id;
                $this->deliveryOrder->cust_po = $this->cust_po;
                $this->deliveryOrder->remark = $this->remark ?? null;
                $this->deliveryOrder->total_amount = $this->total_amount;
                $this->deliveryOrder->customer_snapshot_id = $customerSnapshot->id;
                $this->deliveryOrder->status = $isDraft ? 'Save to Draft' : 'Completed';
                $this->deliveryOrder->save();

                // Delete existing items
                DeliveryOrderItem::where('do_id', $this->deliveryOrder->id)->delete();
            } else {
                // Create customer snapshot
                $customer = Customer::find($this->cust_id);
                $customerSnapshot = CustomerSnapshot::create([
                    'customer_id' => $customer->id,
                    'account' => $customer->account,
                    'cust_name' => $customer->cust_name,
                    'address_line1' => $customer->address_line1,
                    'address_line2' => $customer->address_line2,
                    'address_line3' => $customer->address_line3,
                    'address_line4' => $customer->address_line4,
                    'phone_num' => $customer->phone_num,
                    'fax_num' => $customer->fax_num,
                    'email' => $customer->email,
                    'area' => $customer->area,
                    'term' => $customer->term,
                    'business_registration_no' => $customer->business_registration_no,
                    'gst_registration_no' => $customer->gst_registration_no,
                    'pricing_tier' => $customer->pricing_tier,
                    'currency' => $customer->currency,
                ]);

                // Create new Delivery Order
                $this->deliveryOrder = DeliveryOrder::create([
                    'do_num' => $this->do_num,
                    'ref_num' => $this->ref_num,
                    'date' => $this->date,
                    'cust_id' => $this->cust_id,
                    'user_id' => auth()->id(),
                    'salesman_id' => $this->salesman_id,
                    'cust_po' => $this->cust_po,
                    'remark' => $this->remark ?? null,
                    'total_amount' => $this->total_amount,
                    'customer_snapshot_id' => $customerSnapshot->id,
                    'status' => $isDraft ? 'Save to Draft' : 'Completed',
                ]);
            }
            
            // Process each item in the delivery order (create/update DO items records only)
            foreach ($this->stackedItems as $item) {
                $itemId = $item['item']['id'];
                DeliveryOrderItem::create([
                    'do_id' => $this->deliveryOrder->id,
                    'item_id' => $itemId,
                    'custom_item_name' => $item['custom_item_name'] ?? null,
                    'qty' => $item['item_qty'],
                    'unit_price' => $item['item_unit_price'],
                    'pricing_tier' => $item['pricing_tier'] ?? null,
                    'more_description' => $item['more_description'] ?? null,
                    'amount' => $item['item_qty'] * $item['item_unit_price'],
                ]);
            }

            // Handle stock changes for new delivery orders
            if ($isNewDeliveryOrder && !$isDraft) {
                // Validate stock availability before deducting
                foreach ($this->stackedItems as $item) {
                    $itemId = $item['item']['id'];
                    $qty = (int) ($item['item_qty'] ?? 0);
                    $availableStock = $item['item']['qty'] ?? 0;
                    
                    if ($qty > 0 && $qty > $availableStock) {
                        throw new \Exception("Insufficient stock for item {$item['item']['item_code']}. Available: {$availableStock}, Requested: {$qty}");
                    }
                }
                
                // For new DOs going to Completed status, deduct stock
                foreach ($this->stackedItems as $item) {
                    $itemId = $item['item']['id'];
                    $qty = (int) ($item['item_qty'] ?? 0);
                    
                    if ($qty > 0) {
                        $this->deductFromBatchesFifo($itemId, $qty, false);
                        
                        // Update item qty to reflect current batches
                        $itemRecord = Item::find($itemId);
                        if ($itemRecord) {
                            $itemRecord->qty = BatchTracking::where('item_id', $itemId)->sum('quantity');
                            $itemRecord->save();
                            $this->checkStockAlertLevel($itemRecord);
                        }
                    }
                }
            }

            DB::commit();
            if (!$this->isPreviewMode) {
                // Check if we're changing from Completed to Draft
                $wasCompleted = $this->deliveryOrder && $this->deliveryOrder->status === 'Completed';
                if ($isDraft && $wasCompleted) {
                    toastr()->success('All item quantities have been restored and status changed to draft');
                } else {
                    toastr()->success($isDraft ? 'Delivery Order saved to draft' : 'Delivery Order saved');
                }
            }
            
            // Decide where to go after successful saving
            $shouldStayOnForm = $this->saveAsDraft === true && !$this->isPreviewMode;
            // Reset draft flag
            $this->saveAsDraft = false;
            if (!$this->isPreviewMode) {
                if ($shouldStayOnForm && $this->deliveryOrder && $this->deliveryOrder->id) {
                    // Stay on the form by navigating to the edit route of this draft
                    return redirect()->route('delivery-orders.edit', $this->deliveryOrder->id);
                }
                return $this->redirect('/delivery-orders', navigate: true);
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            if (!$this->isPreviewMode) {
                toastr()->error('An error occurred while processing the Delivery Order: ' . $e->getMessage());
            }
            // Don't redirect on error - stay on the form so user can fix the issue
            return;
        }
    }

    /**
     * Revert previous stock-out transactions and batch deductions for the current DO.
     * This restores batch quantities and item qty to state before this DO.
     */
    private function revertPreviousDoStock(): void
    {
        if (!$this->deliveryOrder || !$this->deliveryOrder->id) {
            return;
        }

        // Fetch previous DO items and reverse their batch deductions in reverse order of creation
        $doItems = DeliveryOrderItem::where('do_id', $this->deliveryOrder->id)->get();

        foreach ($doItems as $doItem) {
            $itemId = $doItem->item_id;

            // Retrieve transactions created for this DO and this item (Stock Out)
            $transactions = Transaction::where('item_id', $itemId)
                ->where('source_type', 'DO')
                ->where('source_doc_num', $this->deliveryOrder->do_num)
                ->where('transaction_type', 'Stock Out')
                ->orderBy('created_at', 'desc')
                ->get();

            $restoredQty = 0;

            foreach ($transactions as $txn) {
                if ($txn->batch_id) {
                    $batch = BatchTracking::find($txn->batch_id);
                    if ($batch) {
                        $batch->quantity += $txn->transaction_qty;
                        $batch->save();
                        $restoredQty += $txn->transaction_qty;
                        
                        // Record the reversal transaction for audit trail
                        Transaction::create([
                            'item_id' => $itemId,
                            'qty_on_hand' => BatchTracking::where('item_id', $itemId)->sum('quantity'),
                            'qty_before' => $txn->qty_after, // Previous state after deduction
                            'qty_after' => $txn->qty_after + $txn->transaction_qty, // Restored state
                            'transaction_qty' => $txn->transaction_qty,
                            'transaction_type' => 'Stock In', // Reversal is treated as stock in
                            'user_id' => auth()->id(),
                            'source_type' => 'DO Reversal',
                            'source_doc_num' => $this->deliveryOrder->do_num,
                            'batch_id' => $txn->batch_id,
                        ]);
                    }
                }
            }

            // Update item qty to reflect restored batches only if we actually restored something
            if ($restoredQty > 0) {
                $itemRecord = Item::find($itemId);
                if ($itemRecord) {
                    $itemRecord->qty = BatchTracking::where('item_id', $itemId)->sum('quantity');
                    $itemRecord->save();
                }
            }
        }
    }

    /**
     * Reconcile stock based on deltas between previous DO quantities and new input.
     * Positive delta => deduct (Stock Out). Negative delta => restore (Stock In).
     */
    private function reconcileDoStockDeltas(array $previousQtyByItem, array $newQtyByItem, bool $isDraft = false): void
    {
        // Union of all item ids
        $allItemIds = collect(array_keys($previousQtyByItem))
            ->merge(array_keys($newQtyByItem))
            ->unique()
            ->values();

        foreach ($allItemIds as $itemId) {
            $prevQty = (int)($previousQtyByItem[$itemId] ?? 0);
            $newQty = (int)($newQtyByItem[$itemId] ?? 0);
            $delta = $newQty - $prevQty; // >0 means additional deduction needed; <0 means restore

            if ($delta === 0) {
                continue; // No change
            }

            if ($delta > 0) {
                // Need to deduct delta quantity using FIFO batches
                $this->deductFromBatchesFifo($itemId, $delta, $isDraft);
            } else {
                // Need to restore -delta quantity back to the same DO's prior batch transactions if possible,
                // otherwise put back into oldest batches
                $this->restoreToBatchesFromDoTransactions($itemId, -$delta, $isDraft);
            }

            // Update item qty to reflect current batches
            $itemRecord = Item::find($itemId);
            if ($itemRecord) {
                $itemRecord->qty = BatchTracking::where('item_id', $itemId)->sum('quantity');
                $itemRecord->save();
                $this->checkStockAlertLevel($itemRecord);
            }
        }
    }

    private function deductFromBatchesFifo(int $itemId, int $deductQty, bool $isDraft = false): void
    {
        $batches = BatchTracking::where('item_id', $itemId)
            ->where('quantity', '>', 0)
            ->orderBy('received_date', 'asc')
            ->get();

        $totalBatchQuantity = $batches->sum('quantity');
        if ($totalBatchQuantity < $deductQty) {
            throw new \Exception("Insufficient stock for item ID {$itemId}");
        }

        $currentQtyOnHand = $totalBatchQuantity;
        $baseTimestamp = now();

        foreach ($batches as $index => $batch) {
            if ($deductQty <= 0) break;
            $take = min($deductQty, $batch->quantity);
            $qtyBefore = $currentQtyOnHand;
            $batch->quantity -= $take;
            $batch->save();
            $currentQtyOnHand -= $take;

            Transaction::create([
                'item_id' => $itemId,
                'qty_on_hand' => $currentQtyOnHand,
                'qty_before' => $qtyBefore,
                'qty_after' => $currentQtyOnHand,
                'transaction_qty' => $take,
                'transaction_type' => 'Stock Out',
                'user_id' => auth()->id(),
                'source_type' => $isDraft ? 'DO Draft Delta' : 'DO',
                'source_doc_num' => $this->do_num,
                'batch_id' => $batch->id,
                'created_at' => $baseTimestamp->copy()->subSeconds($index * 0.01),
                'updated_at' => $baseTimestamp->copy()->subSeconds($index * 0.01)
            ]);

            $deductQty -= $take;
        }
    }

    private function restoreToBatchesFifo(int $itemId, int $restoreQty): void
    {
        // Find the oldest batch to restore stock to
        $oldestBatch = BatchTracking::where('item_id', $itemId)
            ->orderBy('received_date', 'asc')
            ->first();
            
        if ($oldestBatch) {
            $qtyBefore = BatchTracking::where('item_id', $itemId)->sum('quantity');
            $oldestBatch->quantity += $restoreQty;
            $oldestBatch->save();
            $qtyAfter = BatchTracking::where('item_id', $itemId)->sum('quantity');

            Transaction::create([
                'item_id' => $itemId,
                'qty_on_hand' => $qtyAfter,
                'qty_before' => $qtyBefore,
                'qty_after' => $qtyAfter,
                'transaction_qty' => $restoreQty,
                'transaction_type' => 'Stock In',
                'user_id' => auth()->id(),
                'source_type' => 'DO Status Reversal',
                'source_doc_num' => $this->do_num,
                'batch_id' => $oldestBatch->id,
            ]);
        }
    }

    private function restoreToBatchesFromDoTransactions(int $itemId, int $restoreQty, bool $isDraft = false): void
    {
        // First, restore based on previous transactions of this DO (most recent first)
        $transactions = Transaction::where('item_id', $itemId)
            ->where('source_type', 'DO')
            ->where('source_doc_num', $this->do_num)
            ->where('transaction_type', 'Stock Out')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($transactions as $txn) {
            if ($restoreQty <= 0) break;
            if (!$txn->batch_id) continue;
            $batch = BatchTracking::find($txn->batch_id);
            if (!$batch) continue;

            $putBack = min($restoreQty, $txn->transaction_qty);
            $qtyBefore = BatchTracking::where('item_id', $itemId)->sum('quantity');

            $batch->quantity += $putBack;
            $batch->save();

            $qtyAfter = BatchTracking::where('item_id', $itemId)->sum('quantity');

            Transaction::create([
                'item_id' => $itemId,
                'qty_on_hand' => $qtyAfter,
                'qty_before' => $qtyBefore,
                'qty_after' => $qtyAfter,
                'transaction_qty' => $putBack,
                'transaction_type' => 'Stock In',
                'user_id' => auth()->id(),
                'source_type' => $isDraft ? 'DO Draft Delta' : 'DO Delta Reversal',
                'source_doc_num' => $this->do_num,
                'batch_id' => $txn->batch_id,
            ]);

            $restoreQty -= $putBack;
        }

        if ($restoreQty > 0) {
            // No matching prior transactions left; put back into the oldest batch
            $oldestBatch = BatchTracking::where('item_id', $itemId)
                ->orderBy('received_date', 'asc')
                ->first();
            if ($oldestBatch) {
                $qtyBefore = BatchTracking::where('item_id', $itemId)->sum('quantity');
                $oldestBatch->quantity += $restoreQty;
                $oldestBatch->save();
                $qtyAfter = BatchTracking::where('item_id', $itemId)->sum('quantity');

                Transaction::create([
                    'item_id' => $itemId,
                    'qty_on_hand' => $qtyAfter,
                    'qty_before' => $qtyBefore,
                    'qty_after' => $qtyAfter,
                    'transaction_qty' => $restoreQty,
                    'transaction_type' => 'Stock In',
                    'user_id' => auth()->id(),
                    'source_type' => $isDraft ? 'DO Draft Delta' : 'DO Delta Reversal',
                    'source_doc_num' => $this->do_num,
                    'batch_id' => $oldestBatch->id,
                ]);
            }
        }
    }

    public function saveDraft()
    {
        // Mark to save as draft (deductions skipped in addDO)
        $this->saveAsDraft = true;
        return $this->addDO();
    }

        public function preview()
    {
        if ($this->isView) {
            return;
        }
        
        // If order is already completed, don't change status or stock
        if ($this->deliveryOrder && $this->deliveryOrder->status === 'Completed') {
            return redirect()->route('print.delivery-order.preview', $this->deliveryOrder->id);
        }
        
        // Ensure we have a saved draft, then redirect to print preview
        $this->isPreviewMode = true;
        $this->saveDraft();
        $this->isPreviewMode = false;
        if ($this->deliveryOrder && $this->deliveryOrder->id) {
            return redirect()->route('print.delivery-order.preview', $this->deliveryOrder->id);
        }
    }

    
    public function render()
    {
        $this->date = $this->date ?? now()->toDateString();
        $this->do_num = $this->do_num ?? 'DO' . time();
        $this->user_id = $this->user_id ?? auth()->id();
        // Load salesmen list sorted by name for dropdown
        $this->salesmen = User::role('Salesperson')->orderBy('name','asc')->get();
        return view('livewire.d-o-form')->layout('layouts.app');
    }

    private function getLatestDOPriceForItem($itemId, $customerId = null)
    {
        $query = DeliveryOrderItem::where('item_id', $itemId)
            ->whereHas('deliveryOrder', function($q) use ($customerId) {
                if ($customerId) {
                    $q->where('cust_id', $customerId);
                }
            })
            ->orderByDesc('created_at');

        $latestItem = $query->first();
        return $latestItem?->unit_price;
    }

    private function getLatestDODateForItem($itemId, $customerId = null)
    {
        $query = DeliveryOrderItem::where('item_id', $itemId)
            ->whereHas('deliveryOrder', function($q) use ($customerId) {
                if ($customerId) {
                    $q->where('cust_id', $customerId);
                }
            })
            ->with('deliveryOrder')
            ->orderByDesc('created_at');

        $latestItem = $query->first();
        return $latestItem?->deliveryOrder?->date;
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
                'reorder_qty' => $reorder_qty,
                'cost_per_unit' => $item->cost,
                'total_cost' => ($reorder_qty * $item->cost),
                'sup_id' => $item->sup_id,
                'remarks' => ''
            ]);
        }
    }
}
}