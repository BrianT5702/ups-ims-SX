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
    public $lastValidRemark = '';
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
    public array $lastValidDescriptions = [];

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
            $this->lastValidRemark = $deliveryOrder->remark ?? '';
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
                    'details' => $doItem->item->details,
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
                $connection = session('active_db') ?: DB::getDefaultConnection();
                $this->salesmanSearchTerm = optional(User::on($connection)->find($this->salesman_id))->name;
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
    public function searchSalesman()
    {
        // Deprecated; using dropdown list
        $this->salesmanSearchResults = [];
    }
    
    public function selectSalesman($salesmanId)
    {
        if (!$this->isView) {
            $connection = session('active_db') ?: DB::getDefaultConnection();
            $this->selectedSalesman = User::on($connection)->find($salesmanId);
    
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
            // Show all items regardless of stock level - allow out of stock items
            $query = Item::where('item_code', 'like', '%' . $this->itemSearchTerm . '%')
                ->orWhere('item_name', 'like', '%' . $this->itemSearchTerm . '%');
            
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

            $itemExists = false;

            foreach ($this->stackedItems as $key => $stackedItem) {
                if ($stackedItem['item']['id'] === $item->id) {
                    // Always allow incrementing quantity - quantity changes don't affect row count
                    $this->stackedItems[$key]['item_qty'] += 1;
                    $this->stackedItems[$key]['amount'] = 
                        $this->stackedItems[$key]['item_qty'] * $this->stackedItems[$key]['item_unit_price'];
                    $itemExists = true;
                    break;
                }
            }

            if (!$itemExists) {
                // DO MUST FIT ON ONE PAGE - check current rows first
                $maxRows = $this->calculateMaxRows();
                $currentRows = $this->estimateTotalRows(false);
                
                // Block adding if already at or over limit
                if ($currentRows >= $maxRows) {
                    toastr()->error('⚠️ PAGE LIMIT REACHED: Cannot add item. Current: ' . $currentRows . ' rows, Maximum: ' . $maxRows . ' rows. Please remove items, shorten descriptions, or shorten remarks before adding new items.');
                    $this->dispatch('show-limit-error', ['message' => 'Page limit reached (' . $currentRows . '/' . $maxRows . ' rows). Remove items or shorten content to add more.']);
                    return;
                }
                
                // DO MUST FIT ON ONE PAGE - estimate based on rows
                // Calculate estimated rows for current items + new item
                $estimatedRows = $this->estimateTotalRows(true); // true = include new item
                
                if ($estimatedRows > $maxRows) {
                    toastr()->error('⚠️ LIMIT EXCEEDED: Cannot add item. Adding this item would result in ' . $estimatedRows . ' rows (max: ' . $maxRows . ' rows). Please remove items, shorten descriptions (including removing newlines), or shorten remarks to fit on a single page.');
                    $this->dispatch('show-limit-error', ['message' => 'Would exceed limit (' . $estimatedRows . '/' . $maxRows . ' rows). Remove items or shorten content first.']);
                    return;
                }
                
                // Show warning if getting close to limit
                if ($estimatedRows > ($maxRows * 0.9)) {
                    toastr()->warning('Adding this item will bring you close to the one-page limit (' . $estimatedRows . ' rows, max ' . $maxRows . ' rows).');
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
                    'details' => $item->details,
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
            $this->calculateTotalAmount();
        }
    }
    
    public function removeLastItemIfExceedsPage()
    {
        if (!$this->isView && count($this->stackedItems) > 0) {
            // Remove the last item that was added
            array_pop($this->stackedItems);
            $this->stackedItems = array_values($this->stackedItems);
            $this->calculateTotalAmount();
            toastr()->error('Cannot add item: Content would exceed one page limit. Please remove items or shorten descriptions to fit on a single page.');
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
            
            // No stock validation - allow any quantity, including negative stock
            $item['item_qty'] = $requestedQty;
            $item['item_unit_price'] = floatval($item['item_unit_price'] ?? 0);
            $this->stackedItems[$index]['amount'] = $item['item_qty'] * $item['item_unit_price'];

            $this->calculateTotalAmount();
        }
    }
    
    public function updatedStackedItems($value, $key)
    {
        // This method is called when any stackedItems property is updated
        // Handle item_qty updates
        if (str_contains($key, '.item_qty')) {
            $index = explode('.', $key)[1];
            $this->updatePriceLine($index);
        }
        
        // Check if more_description was updated - validate immediately when the model updates (triggered on blur via wire:model.lazy)
        if (strpos($key, 'more_description') !== false) {
            // Extract index from key (e.g., "stackedItems.0.more_description" -> 0)
            $parts = explode('.', $key);
            if (count($parts) >= 2 && is_numeric($parts[1])) {
                $index = (int)$parts[1];
                $this->validateAndMaybeRevertDescription($index);
            }
        }
    }
    
    private function validateAndMaybeRevertDescription(int $index): void
    {
        $currentDesc = $this->stackedItems[$index]['more_description'] ?? '';
        $lastValidDesc = $this->lastValidDescriptions[$index] ?? '';

        // Track last valid value per index
        if (!array_key_exists($index, $this->lastValidDescriptions)) {
            $this->lastValidDescriptions[$index] = $currentDesc;
            $lastValidDesc = $currentDesc;
        }

        $maxRows = $this->calculateMaxRows();
        
        // Check current rows without this description change
        $this->stackedItems[$index]['more_description'] = $lastValidDesc;
        $currentRows = $this->estimateTotalRows(false);
        
        // If already at limit, block adding descriptions
        if ($currentRows >= $maxRows) {
            // Keep the last valid description
            $this->stackedItems[$index]['more_description'] = $lastValidDesc;
            toastr()->error('⚠️ PAGE LIMIT REACHED: Cannot add description. Current: ' . $currentRows . ' rows, Maximum: ' . $maxRows . ' rows. Please remove items, shorten other descriptions, or shorten remarks before adding descriptions.');
            $this->dispatch('show-limit-error', ['message' => 'Page limit reached (' . $currentRows . '/' . $maxRows . ' rows). Remove items or shorten content to add descriptions.']);
            return;
        }
        
        // Now check with the new description
        $this->stackedItems[$index]['more_description'] = $currentDesc;
        $estimatedRows = $this->estimateTotalRows(false);
        
        if ($estimatedRows > $maxRows) {
            // Revert to last valid value
            $this->stackedItems[$index]['more_description'] = $lastValidDesc;
            toastr()->error('⚠️ LIMIT EXCEEDED: Cannot add description. Would result in ' . $estimatedRows . ' rows (max: ' . $maxRows . ' rows). Please shorten descriptions/remarks or remove items.');
            $this->dispatch('show-limit-error', ['message' => 'Would exceed limit (' . $estimatedRows . '/' . $maxRows . ' rows). Shorten content or remove items first.']);
        } else {
            // Update last valid value
            $this->lastValidDescriptions[$index] = $currentDesc;
            if ($estimatedRows > ($maxRows * 0.95)) {
                toastr()->warning('Content is very close to one-page limit (' . $estimatedRows . ' rows, max ' . $maxRows . ').');
            }
        }
    }
    
    public function checkDescriptionLimit($index)
    {
        // Check if current description length would exceed page limit
        // Called on blur event or by JavaScript
        $estimatedRows = $this->estimateTotalRows(false);
        $maxRows = $this->calculateMaxRows();
        
        if ($estimatedRows > $maxRows) {
            toastr()->error('Description would exceed the one-page limit! (' . $estimatedRows . ' rows, max ' . $maxRows . ' rows). Please shorten this description, other descriptions, or remove items.');
            return [
                'exceeded' => true,
                'message' => 'Description would exceed the one-page limit (' . $estimatedRows . ' rows, max ' . $maxRows . ' rows). Please shorten this description, other descriptions, or remove items.',
                'currentRows' => $estimatedRows,
                'maxRows' => $maxRows
            ];
        }
        
        return ['exceeded' => false];
    }
    
    public function validateDescriptionLengthRealtime()
    {
        // Validation when description is updated - only show warnings, not errors
        // Errors are shown on blur via checkDescriptionLimit
        $estimatedRows = $this->estimateTotalRows(false);
        $maxRows = $this->calculateMaxRows();
        
        if ($estimatedRows > $maxRows) {
            // Don't show error here - let checkDescriptionLimit handle it on blur
            // This prevents interrupting typing
        } elseif ($estimatedRows > ($maxRows * 0.95)) {
            // Only show warning if very close, and only once to avoid spam
            static $lastWarning = 0;
            if (time() - $lastWarning > 3) { // Throttle warnings to every 3 seconds
                toastr()->warning('Content is very close to one page limit (' . $estimatedRows . ' rows, max ' . $maxRows . ' rows).');
                $lastWarning = time();
            }
        }
    }
    
    public function updatedRemark($value)
    {
        // Validate page limit when remark is updated
        $this->validateDescriptionLength();
    }
    
    public function validateDescriptionLength()
    {
        // DO MUST FIT ON ONE PAGE - check based on rows (includes items, descriptions, and remarks)
        // This is called on blur/change events
        
        $maxRows = $this->calculateMaxRows();
        $currentRemark = $this->remark ?? '';
        
        // Initialize last valid remark if not set
        if ($this->lastValidRemark === '') {
            $this->lastValidRemark = $currentRemark;
        }
        
        // Check current rows with last valid remark
        $tempRemark = $this->remark;
        $this->remark = $this->lastValidRemark;
        $currentRows = $this->estimateTotalRows(false);
        
        // If already at limit, block adding to remark
        if ($currentRows >= $maxRows) {
            $this->remark = $this->lastValidRemark;
            toastr()->error('⚠️ PAGE LIMIT REACHED: Cannot add to remark. Current: ' . $currentRows . ' rows, Maximum: ' . $maxRows . ' rows. Please remove items, shorten descriptions, or remove lines from remark before adding more.');
            $this->dispatch('show-limit-error', ['message' => 'Page limit reached (' . $currentRows . '/' . $maxRows . ' rows). Remove items or shorten content to add to remark.']);
            return;
        }
        
        // Now check with the new remark
        $this->remark = $tempRemark;
        $estimatedRows = $this->estimateTotalRows(false);
        
        if ($estimatedRows > $maxRows) {
            // Revert to last valid remark
            $this->remark = $this->lastValidRemark;
            toastr()->error('⚠️ LIMIT EXCEEDED: Cannot add to remark. Would result in ' . $estimatedRows . ' rows (max: ' . $maxRows . ' rows). Please remove items, shorten descriptions, or remove lines from remark.');
            $this->dispatch('show-limit-error', ['message' => 'Would exceed limit (' . $estimatedRows . '/' . $maxRows . ' rows). Remove items or shorten content first.']);
        } else {
            // Update last valid remark
            $this->lastValidRemark = $currentRemark;
            if ($estimatedRows > ($maxRows * 0.9)) {
                toastr()->warning('Content is getting close to one page limit (' . $estimatedRows . ' rows, max ' . $maxRows . ' rows). Please keep descriptions and remarks short to ensure the DO fits on one page.');
            }
        }
    }
    
    /**
     * Estimate total rows needed for all items + remarks
     * Each item = 1 base row + additional rows for descriptions
     * Remarks = estimated rows based on text length
     */
    private function estimateTotalRows($includeNewItem = false)
    {
        $totalRows = 0;
        $items = $this->stackedItems;
        
        // Base row height: ~25px (padding 4px top + 4px bottom = 8px, font 0.85em with line-height 1.3 ≈ 17px)
        // Description rows: estimate based on text length and wrapping
        // Average characters per line in description column: ~60-70 chars (depends on column width)
        
        foreach ($items as $stackedItem) {
            $totalRows += 1; // Base row for each item
            
            // Estimate description rows - MUST account for actual newlines (Enter key)
            $desc = $stackedItem['more_description'] ?? '';
            if (!empty($desc)) {
                // Count actual newlines first (each \n = 1 new line)
                $newlineCount = substr_count($desc, "\n");
                $descLines = $newlineCount + 1; // At least 1 line even if no newlines
                
                // For each line, estimate if it wraps (if line length > 60 chars - more conservative)
                $lines = explode("\n", $desc);
                $totalDescRows = 0;
                foreach ($lines as $line) {
                    $lineLength = strlen($line);
                    // If line is longer than 60 chars, it will wrap (more conservative estimate)
                    $wrappedLines = max(1, ceil($lineLength / 60));
                    $totalDescRows += $wrappedLines;
                }
                
                // Additional rows beyond base = total description rows - 1 (since base row already counted)
                $totalRows += ($totalDescRows - 1);
            }

            // Estimate details rows (item details shown as bullet list)
            $details = $stackedItem['item']['details'] ?? '';
            if (!empty($details)) {
                // Split by newline and wrap each line (~60 chars per line, similar to descriptions)
                $detailLines = explode("\n", $details);
                $detailRows = 0;
                foreach ($detailLines as $line) {
                    $line = trim($line);
                    if ($line === '') continue;
                    $lineLength = strlen($line);
                    $wrappedLines = max(1, ceil($lineLength / 60));
                    $detailRows += $wrappedLines;
                }
                // Each detail line is an additional row (bulleted list under the item)
                $totalRows += $detailRows;
            }
        }
        
        // If including new item, add 1 more row
        if ($includeNewItem) {
            $totalRows += 1;
        }
        
        // Add rows for remarks if present
        $remark = $this->remark ?? '';
        if (!empty($remark)) {
            // Remark section calculation (including all padding and borders):
            // - Wrapper padding-top: 10px
            // - Inner padding: 8px top + 8px bottom = 16px
            // - Border: 1px top + 1px bottom = 2px
            // - Content: font 0.85em of 15px = 12.75px, line-height 1.3 = 16.575px per line
            // - Base (label + 1 line): 16.575px
            // Total base: 10px + 16px + 2px + 16.575px = 44.575px ≈ 45px
            // At ~30px per row: 45px ÷ 30px = 1.5 rows ≈ 2 rows base
            
            // Count actual newlines - each line counts as 1 row
            // MUST account for actual newlines (Enter key presses)
            $remarkLines = explode("\n", $remark);
            $lineCount = count($remarkLines);
            
            // Base remark section = ~2 rows equivalent (padding + border + label)
            // Each line in remark = 1 row
            // Total: 2 base rows + number of lines
            $totalRows += 2 + $lineCount;
        }
        
        return $totalRows;
    }
    
    /**
     * Calculate maximum rows that fit on one page
     */
    private function calculateMaxRows()
    {
        // Page dimensions: Letter size (11in = 279.4mm)
        // Margins: 0.75cm top + 0.75cm bottom = 15mm total
        // Usable height: 279.4mm - 15mm = 264.4mm
        // Convert to pixels at 96 DPI: (264.4 / 25.4) * 96 ≈ 998px
        // With 5% reduction (matching preview): 998px * 0.95 ≈ 948px
        
        // Account for fixed elements (updated with reduced footer padding):
        // - Header (company info + customer info): ~120px
        // - Table header row: ~25px
        // - Signature section: ~80px
        // - Footer padding-top: ~8px (reduced from 18px, saving 10px)
        // - Padding/margins: ~40px
        // Total fixed: ~273px
        // Available for item rows: 948px - 273px = 675px
        
        // Row height: base ~25px + description lines
        // Average row height: ~25-30px (assuming some items have short descriptions)
        // Use more accurate 28px average row height (between 25-30px)
        // Max rows: 675px / 28px ≈ 24.1 rows
        
        // Round down to 21 rows to ensure reliable fit
        // This accounts for items with longer descriptions
        
        return 21; // Hard cap: at most 21 rows per page
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

        // No stock validation - allow negative stock values
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
                        // Special case: Deduct stock when changing from Draft to Completed
                        // Allow negative stock - no validation
                        foreach ($this->stackedItems as $item) {
                            $itemId = $item['item']['id'];
                            $qty = (int) ($item['item_qty'] ?? 0);
                            
                            if ($qty > 0) {
                                $this->deductFromBatchesFifo($itemId, $qty, false);
                                
                                // Update item qty to reflect current batches (can be negative)
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
                // For new DOs going to Completed status, deduct stock
                // Allow negative stock - no validation
                foreach ($this->stackedItems as $item) {
                    $itemId = $item['item']['id'];
                    $qty = (int) ($item['item_qty'] ?? 0);
                    
                    if ($qty > 0) {
                        $this->deductFromBatchesFifo($itemId, $qty, false);
                        
                        // Update item qty to reflect current batches (can be negative)
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
        // Get all batches for this item (including zero/negative quantities)
        $batches = BatchTracking::where('item_id', $itemId)
            ->orderBy('received_date', 'asc')
            ->get();

        // If no batches exist, create one to allow negative stock tracking
        if ($batches->isEmpty()) {
            $batch = BatchTracking::create([
                'batch_num' => 'AUTO-' . now()->format('YmdHis'),
                'item_id' => $itemId,
                'quantity' => 0,
                'received_date' => now(),
                'received_by' => auth()->id()
            ]);
            $batches = collect([$batch]);
        }

        $currentQtyOnHand = BatchTracking::where('item_id', $itemId)->sum('quantity');
        $baseTimestamp = now();
        $remainingDeductQty = $deductQty;

        // Deduct from batches in FIFO order, allowing negative values
        foreach ($batches as $index => $batch) {
            if ($remainingDeductQty <= 0) break;
            
            $qtyBefore = $currentQtyOnHand;
            
            // Take from this batch - if batch has stock, take up to its quantity, otherwise take all remaining
            if ($batch->quantity > 0) {
                $take = min($remainingDeductQty, $batch->quantity);
            } else {
                // Batch is empty or negative, take all remaining quantity from this batch
                $take = $remainingDeductQty;
            }
            
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

            $remainingDeductQty -= $take;
        }
        
        // If there's still quantity to deduct after processing all batches, 
        // deduct from the last batch to allow negative stock
        if ($remainingDeductQty > 0) {
            $lastBatch = $batches->last();
            $qtyBefore = $currentQtyOnHand;
            $lastBatch->quantity -= $remainingDeductQty;
            $lastBatch->save();
            $currentQtyOnHand -= $remainingDeductQty;

            Transaction::create([
                'item_id' => $itemId,
                'qty_on_hand' => $currentQtyOnHand,
                'qty_before' => $qtyBefore,
                'qty_after' => $currentQtyOnHand,
                'transaction_qty' => $remainingDeductQty,
                'transaction_type' => 'Stock Out',
                'user_id' => auth()->id(),
                'source_type' => $isDraft ? 'DO Draft Delta' : 'DO',
                'source_doc_num' => $this->do_num,
                'batch_id' => $lastBatch->id,
                'created_at' => $baseTimestamp->copy()->subSeconds($batches->count() * 0.01),
                'updated_at' => $baseTimestamp->copy()->subSeconds($batches->count() * 0.01)
            ]);
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
        // Use current database connection (not just 'ups') to match validation
        $connection = session('active_db') ?: DB::getDefaultConnection();
        $this->salesmen = User::on($connection)->role('Salesperson')->orderBy('name','asc')->get();
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