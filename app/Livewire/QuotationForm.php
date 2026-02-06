<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\DeliveryOrderItem;
use App\Models\Customer;
use App\Models\Item;
use App\Models\User;
use App\Models\CustomerSnapshot;
use App\Rules\UniqueInCurrentDatabase;
use App\Rules\ExistsInCurrentDatabase;
use Illuminate\Support\Facades\DB;

#[Title('UR | Manage Quotation')]
class QuotationForm extends Component
{
    public $isView = false;
    public $quotation = null;

    public $stackedItems = [];

    public $quotation_num;
    public $ref_num;
    public $cust_id;
    public $selectedCustomer = null;
    public $salesman_id;
    public $date;
    public $remark;
    public $status = 'Save to Draft'; // Save to Draft | Sent

    public $itemSearchTerm = '';
    public $itemSearchResults = [];
    public $itemHighlightIndex = -1;

    public $customerSearchTerm = '';
    public $customerSearchResults = [];

    public $salesmen = [];

    // Totals
    public $total_amount = 0; // sum of line amounts

    public $isRevising = false;
    public $backupStackedItems = [];

    // Track when we're saving only to enable preview navigation without duplicate toasts
    public bool $isPreviewMode = false;

    public function mount(Quotation $quotation)
    {
        $this->isView = request()->routeIs('quotations.view');

        if ($quotation && $quotation->id) {
            $this->quotation = $quotation;
            $this->quotation_num = $quotation->quotation_num;
            $this->ref_num = $quotation->ref_num;
            $this->cust_id = $quotation->cust_id;
            $this->selectedCustomer = $quotation->customer;
            $this->salesman_id = $quotation->salesman_id;
            $this->date = $quotation->date;
            $this->remark = $quotation->remark;
            $this->status = $quotation->status;
            $this->total_amount = floatval($quotation->total_amount ?? 0);

            $this->stackedItems = [];
            foreach ($quotation->items as $qItem) {
                $this->stackedItems[] = [
                    'item' => [
                        'id' => $qItem->item->id,
                        'item_code' => $qItem->item->item_code,
                        'item_name' => $qItem->item->item_name,
                        'qty' => $qItem->item->qty,
                        'cost' => $qItem->item->cost,
                        'cust_price' => $qItem->item->cust_price,
                        'term_price' => $qItem->item->term_price,
                        'cash_price' => $qItem->item->cash_price,
                        // Latest previously quoted price for this customer
                        'latest_quote_price' => $this->getLatestQuotationPriceForItem($qItem->item->id, $this->cust_id),
                        'latest_quote_date' => $this->getLatestQuotationDateForItem($qItem->item->id, $this->cust_id),
                    ],
                    'item_qty' => $qItem->qty,
                    'pricing_tier' => $qItem->pricing_tier ?? '',
                    'item_unit_price' => $qItem->unit_price,
                    'amount' => $qItem->amount,
                    'more_description' => $qItem->more_description,
                    'custom_item_name' => $qItem->custom_item_name ?? $qItem->item->item_name,
                    'price_manually_modified' => empty($qItem->pricing_tier),
                ];
            }

            if ($quotation->customer) {
                $this->customerSearchTerm = $quotation->customer->cust_name;
            }
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
        if ($this->isView) { return; }
        $this->selectedCustomer = Customer::find($custId);
        $this->cust_id = $custId;
        $this->customerSearchTerm = $this->selectedCustomer->cust_name;
        $this->customerSearchResults = [];
        
        // Auto-select salesman from customer if available
        if ($this->selectedCustomer && $this->selectedCustomer->salesman_id) {
            $this->salesman_id = $this->selectedCustomer->salesman_id;
        }
    }

    public function updatedItemSearchTerm()
    {
        if ($this->isView) { return; }
        $this->searchItems();
        $this->itemHighlightIndex = (count($this->itemSearchResults) > 0) ? 0 : -1;
    }

    public function searchItems()
    {
        if (!empty($this->itemSearchTerm)) {
            $this->itemSearchResults = Item::where('item_code', 'like', '%' . $this->itemSearchTerm . '%')
                ->orWhere('item_name', 'like', '%' . $this->itemSearchTerm . '%')
                ->orderBy('item_name','asc')
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
        if ($this->isView) { return; }
        $item = Item::find($itemId);
        if (!$item) { return; }

        $itemExists = false;
        foreach ($this->stackedItems as $key => $stackedItem) {
            if ($stackedItem['item']['id'] === $item->id) {
                $this->stackedItems[$key]['item_qty'] += 1;
                $this->stackedItems[$key]['amount'] = $this->stackedItems[$key]['item_qty'] * $this->stackedItems[$key]['item_unit_price'];
                $itemExists = true;
                break;
            }
        }

        if (!$itemExists) {
            // Check if maximum items limit (15) is reached only for new items
            if (count($this->stackedItems) >= 15) {
                toastr()->error('Maximum of 15 items allowed per quotation. Please remove some items before adding new ones.');
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
                    'latest_quote_price' => $this->getLatestQuotationPriceForItem($item->id, $this->cust_id),
                    'latest_quote_date' => $this->getLatestQuotationDateForItem($item->id, $this->cust_id),
                ],
                'item_qty' => 1,
                'pricing_tier' => '',
                'item_unit_price' => 0,
                'amount' => 0,
                'more_description' => null,
                'custom_item_name' => $item->item_name,
                'price_manually_modified' => true,
            ];
        }

        $this->itemSearchTerm = '';
        $this->itemSearchResults = [];
        $this->recalculateTotals();
    }

    public function removeItem($index)
    {
        if ($this->isView) { return; }
        unset($this->stackedItems[$index]);
        $this->stackedItems = array_values($this->stackedItems);
        $this->recalculateTotals();
    }

    public function selectPricingTier($index, $tier)
    {
        if (!isset($this->stackedItems[$index])) { return; }
        $this->stackedItems[$index]['pricing_tier'] = $tier ?: '';
        if ($this->stackedItems[$index]['pricing_tier'] === '') {
            $this->stackedItems[$index]['item_unit_price'] = 0;
            $this->stackedItems[$index]['price_manually_modified'] = true;
            $this->updatePriceLine($index);
        } else {
            $this->updateItemPricing($index);
        }
    }

    public function updateItemPricing($index)
    {
        if (!isset($this->stackedItems[$index])) { return; }
        $pricingTier = $this->stackedItems[$index]['pricing_tier'] ?? '';
        $item = $this->stackedItems[$index]['item'];

        if ($pricingTier && in_array($pricingTier, ['Customer Price', 'Term Price', 'Cash Price', 'Cost', 'Previous Price'])) {
            $tierPrice = match ($pricingTier) {
                'Customer Price' => $item['cust_price'],
                'Term Price' => $item['term_price'],
                'Cash Price' => $item['cash_price'],
                'Cost' => $item['cost'],
                // Use the latest previously quoted price for this customer
                'Previous Price' => ($item['latest_quote_price'] ?? null) ?: $this->getLatestQuotationPriceForItem($item['id'], $this->cust_id),
                default => $item['cust_price']
            };
            $tierPrice = floatval($tierPrice ?? 0);
            $this->stackedItems[$index]['item_unit_price'] = $tierPrice;
            $this->stackedItems[$index]['price_manually_modified'] = false;
        } elseif (empty($pricingTier)) {
            $this->stackedItems[$index]['price_manually_modified'] = true;
            $this->stackedItems[$index]['item_unit_price'] = 0;
        }

        $this->stackedItems[$index]['amount'] = $this->stackedItems[$index]['item_qty'] * $this->stackedItems[$index]['item_unit_price'];
        $this->recalculateTotals();
    }

    private function getLatestQuotationPriceForItem($itemId, $customerId = null)
    {
        $query = QuotationItem::where('item_id', $itemId)
            ->whereHas('quotation', function($q) use ($customerId) {
                if ($customerId) { $q->where('cust_id', $customerId); }
            })
            ->orderByDesc('created_at');
        $latestItem = $query->first();
        return $latestItem?->unit_price;
    }

    private function getLatestQuotationDateForItem($itemId, $customerId = null)
    {
        $query = QuotationItem::where('item_id', $itemId)
            ->whereHas('quotation', function($q) use ($customerId) {
                if ($customerId) { $q->where('cust_id', $customerId); }
            })
            ->with('quotation')
            ->orderByDesc('created_at');
        $latestItem = $query->first();
        return $latestItem?->quotation?->date;
    }

    public function updatePriceLine($index)
    {
        if (!isset($this->stackedItems[$index])) { return; }
        $item = $this->stackedItems[$index];
        $qty = floatval($item['item_qty'] ?? 0);
        $price = floatval($item['item_unit_price'] ?? 0);
        $this->stackedItems[$index]['amount'] = $qty * $price;
        $this->recalculateTotals();
    }

    public function updateUnitPrice($index)
    {
        if (!isset($this->stackedItems[$index])) { return; }
        $currentTier = $this->stackedItems[$index]['pricing_tier'] ?? '';
        if ($currentTier === '' || $currentTier === null) {
            $this->stackedItems[$index]['price_manually_modified'] = true;
            $this->updatePriceLine($index);
        }
    }

    public function updated($prop)
    {
        if ($this->isView) { return; }
        if (preg_match('/stackedItems\\.\\d+\\.(item_qty|item_unit_price)/', $prop)) {
            // Coerce to numeric safe values to avoid string propagation
            if (preg_match('/stackedItems\\.(\\d+)\\.item_qty/', $prop, $m)) {
                $i = (int)$m[1];
                $this->stackedItems[$i]['item_qty'] = intval($this->stackedItems[$i]['item_qty'] ?? 0);
            }
            if (preg_match('/stackedItems\\.(\\d+)\\.item_unit_price/', $prop, $m2)) {
                $i2 = (int)$m2[1];
                $this->stackedItems[$i2]['item_unit_price'] = floatval($this->stackedItems[$i2]['item_unit_price'] ?? 0);
            }
            $this->recalculateTotals();
        }
    }

    public function recalculateTotals()
    {
        $this->total_amount = 0;
        foreach ($this->stackedItems as $key => $item) {
            $qty = intval($item['item_qty'] ?? 0);
            $price = floatval($item['item_unit_price'] ?? 0);
            $line = $qty * $price;
            $this->stackedItems[$key]['amount'] = $line;
            $this->total_amount += $line;
        }
    }

    public function saveDraft()
    {
        $this->status = 'Save to Draft';
        return $this->addQuotation();
    }

    public function markSent()
    {
        $this->status = 'Sent';
        return $this->addQuotation();
    }

    public function toggleRevise()
    {
        if ($this->quotation && $this->status === 'Sent') {
            if (!$this->isRevising) {
                // Entering revise mode: snapshot current values
                $this->backupStackedItems = $this->stackedItems;
                $this->isRevising = true;
            } else {
                // Cancelling revise: restore previous values and do not save
                if (!empty($this->backupStackedItems)) {
                    $this->stackedItems = $this->backupStackedItems;
                }
                $this->recalculateTotals();
                $this->backupStackedItems = [];
                $this->isRevising = false;
                toastr()->info('Revision cancelled');
            }
        }
    }

    public function saveRevision()
    {
        if (!($this->quotation && $this->status === 'Sent' && $this->isRevising)) {
            return;
        }
        if (empty($this->stackedItems)) {
            toastr()->error('At least one item is required to save the revision');
            return;
        }
        $this->validate([
            'stackedItems.*.item_qty' => 'required|integer|min:1',
            'stackedItems.*.item_unit_price' => 'required|numeric|min:0',
        ]);

        try {
            $this->recalculateTotals();

            // Update Quotation totals (keep status as Sent)
            $q = $this->quotation->fresh();
            if ($q) {
                $q->ref_num = $this->ref_num;
                $q->remark = $this->remark ?? null;
                $q->total_amount = $this->total_amount;
                $q->status = 'Sent';
                $q->updated_by = auth()->id();
                $q->save();

                // Replace items
                QuotationItem::where('quotation_id', $q->id)->delete();
                foreach ($this->stackedItems as $item) {
                    $qty = intval($item['item_qty'] ?? 0);
                    $price = floatval($item['item_unit_price'] ?? 0);
                    QuotationItem::create([
                        'quotation_id' => $q->id,
                        'item_id' => $item['item']['id'],
                        'custom_item_name' => $item['custom_item_name'] ?? null,
                        'qty' => $qty,
                        'unit_price' => $price,
                        'pricing_tier' => $item['pricing_tier'] ?? null,
                        'more_description' => $item['more_description'] ?? null,
                        'amount' => $qty * $price,
                    ]);
                }
            }

            $this->isRevising = false;
            // Clear backups so Cancel won't revert after save
            $this->backupStackedItems = [];
            toastr()->success('Revision saved');
            return redirect()->to("/quotations/{$this->quotation->id}/edit");
        } catch (\Exception $e) {
            toastr()->error('Failed to save revision: ' . $e->getMessage());
        }
    }

    public function preview()
    {
        if ($this->isView) { return; }
        // Ensure we have a saved draft, then redirect to print preview
        $this->isPreviewMode = true;
        $this->saveDraft();
        $this->isPreviewMode = false;
        if ($this->quotation && $this->quotation->id) {
            return redirect()->route('print.quotation.preview', $this->quotation->id);
        }
    }

    public function addQuotation()
    {
        if ($this->isView) { return; }

        $this->validate([
            'quotation_num' => ['required', new UniqueInCurrentDatabase('quotations', 'quotation_num', $this->quotation?->id)],
            'cust_id' => ['required', new ExistsInCurrentDatabase('customers', 'id')],
            'salesman_id' => ['required', new ExistsInCurrentDatabase('users', 'id')],
            'date' => 'required|date',
            'stackedItems.*.item_qty' => 'required|integer|min:1',
            'stackedItems.*.item_unit_price' => 'required|numeric|min:0',
        ]);

        // enforce pricing tier when not manual
        foreach ($this->stackedItems as $index => $item) {
            if (!isset($item['price_manually_modified']) || !$item['price_manually_modified']) {
                if (empty($item['pricing_tier']) || !in_array($item['pricing_tier'], ['Customer Price', 'Term Price', 'Cash Price', 'Cost'])) {
                    $this->addError("stackedItems.{$index}.pricing_tier", 'Pricing tier required when not using custom price.');
                }
            }
        }
        if ($this->getErrorBag()->any()) { return; }

        try {
            DB::beginTransaction();

            $this->recalculateTotals();

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

            if ($this->quotation && $this->quotation->id) {
                $q = $this->quotation;
                $q->quotation_num = $this->quotation_num;
                $q->ref_num = $this->ref_num;
                $q->cust_id = $this->cust_id;
                $q->user_id = auth()->id();
                $q->salesman_id = $this->salesman_id;
                $q->date = $this->date;
                $q->remark = $this->remark ?? null;
                $q->total_amount = $this->total_amount;
                $q->customer_snapshot_id = $customerSnapshot->id;
                $q->status = $this->status;
                $q->updated_by = auth()->id();
                $q->save();

                QuotationItem::where('quotation_id', $q->id)->delete();
            } else {
                $this->quotation = Quotation::create([
                    'quotation_num' => $this->quotation_num ?? ('Q' . time()),
                    'ref_num' => $this->ref_num,
                    'cust_id' => $this->cust_id,
                    'user_id' => auth()->id(),
                    'salesman_id' => $this->salesman_id,
                    'date' => $this->date,
                    'remark' => $this->remark ?? null,
                    'total_amount' => $this->total_amount,
                    'customer_snapshot_id' => $customerSnapshot->id,
                    'status' => $this->status,
                ]);
            }

            foreach ($this->stackedItems as $item) {
                QuotationItem::create([
                    'quotation_id' => $this->quotation->id,
                    'item_id' => $item['item']['id'],
                    'custom_item_name' => $item['custom_item_name'] ?? null,
                    'qty' => intval($item['item_qty'] ?? 0),
                    'unit_price' => floatval($item['item_unit_price'] ?? 0),
                    'pricing_tier' => $item['pricing_tier'] ?? null,
                    'more_description' => $item['more_description'] ?? null,
                    'amount' => ($item['item_qty'] ?? 0) * ($item['item_unit_price'] ?? 0),
                ]);
            }

            DB::commit();
            if (!$this->isPreviewMode) {
                toastr()->success('Quotation saved');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            if (!$this->isPreviewMode) {
                toastr()->error('Failed to save quotation: ' . $e->getMessage());
            }
        }

        return redirect()->to('/quotations');
    }

    public function render()
    {
        $this->date = $this->date ?? now()->toDateString();
        $this->quotation_num = $this->quotation_num ?? ('Q' . time());
        // Use current database connection (not just 'ups') to match validation
        $connection = session('active_db') ?: DB::getDefaultConnection();
        $this->salesmen = User::on($connection)->role('Salesperson')->orderBy('name','asc')->get();
        return view('livewire.quotation-form')->layout('layouts.app');
    }
}