<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Livewire\Concerns\ManagesQuotationItemGrid;
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
use Illuminate\Support\Facades\Validator;

#[Title('UR | Manage Quotation')]
class QuotationForm extends Component
{
    use ManagesQuotationItemGrid;

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

    public $customerSearchTerm = '';
    public $customerSearchResults = [];

    public $salesmen = [];

    // Totals
    public $total_amount = 0; // sum of line amounts

    public $isRevising = false;
    public $backupStackedItems = [];

    // Track when we're saving only to enable preview navigation without duplicate toasts
    public bool $isPreviewMode = false;

    public array $lastValidDescriptions = [];

    public string $lastValidRemark = '';

    public function mount(Quotation $quotation)
    {
        $this->isView = request()->routeIs('quotations.view');

        if ($quotation->id) {
            $this->quotation = $quotation;
            $this->quotation_num = $quotation->quotation_num;
            $this->ref_num = $quotation->ref_num;
            $this->cust_id = $quotation->cust_id;
            $this->selectedCustomer = $quotation->customer;
            $this->salesman_id = $quotation->salesman_id;
            $this->date = $quotation->date;
            $this->remark = $quotation->remark;
            $this->lastValidRemark = $quotation->remark ?? '';
            $this->status = $quotation->status;
            $this->total_amount = floatval($quotation->total_amount ?? 0);

            $this->stackedItems = [];
            $this->freeFormTextRows = [];
            $fallbackRow = 0;
            foreach ($quotation->items()->orderByRaw('row_index IS NULL, row_index')->orderBy('id')->get() as $qItem) {
                $rowIndex = $qItem->row_index;
                if ($rowIndex === null) {
                    while ($this->rowIndexOccupiedInStack($fallbackRow)) {
                        $fallbackRow++;
                    }
                    $rowIndex = $fallbackRow;
                    $fallbackRow++;
                }

                if (empty($qItem->item_id)) {
                    $this->hydrateQuotationFreeFormRowFromSaved($qItem, (int) $rowIndex);

                    continue;
                }

                $this->stackedItems[] = $this->hydrateQuotationStackedItemFromSaved($qItem, (int) $rowIndex);
            }
            $this->coalesceFreeFormTextRowsToAnchors();
            $this->pruneEmptyTextOnlyStackedItems();
            $this->seedQuotationLastValidDescriptions();

            if ($quotation->customer) {
                $this->customerSearchTerm = $quotation->customer->cust_name;
            }
        } else {
            $this->quotation = $quotation;
            $connection = session('active_db') ?: DB::getDefaultConnection();
            $this->quotation_num = QuotationNumberService::getNextQuotationNumberPreview($connection);
        }
    }

    public function updatedCustomerSearchTerm()
    {
        if (!$this->isView) {
            // If user types or clears the search box, detach any previously selected customer
            // so validation accurately reflects the current UI state.
            if ($this->selectedCustomer && $this->customerSearchTerm !== ($this->selectedCustomer->cust_name ?? '')) {
                $this->selectedCustomer = null;
                $this->cust_id = null;
            }

            $this->searchCustomers();
        }
    }

    public function searchCustomers()
    {
        $term = trim((string) $this->customerSearchTerm);
        if ($term === '') {
            $this->customerSearchResults = [];

            return;
        }

        $this->customerSearchResults = Customer::query()
            ->select(['id', 'account', 'cust_name'])
            ->autocompleteSearch($term)
            ->limit(25)
            ->get();
    }

    public function selectCustomer($custId)
    {
        if ($this->isView) {
            return;
        }

        $this->selectedCustomer = Customer::find($custId);
        $this->cust_id = $custId;
        $this->customerSearchTerm = $this->selectedCustomer->cust_name;
        $this->customerSearchResults = [];

        if ($this->selectedCustomer && $this->selectedCustomer->salesman_id) {
            $this->salesman_id = $this->selectedCustomer->salesman_id;

            $this->resetErrorBag(['salesman_id']);
            if (method_exists($this, 'resetValidation')) {
                $this->resetValidation(['salesman_id']);
            }
        }

        foreach ($this->stackedItems as $key => $stackedItem) {
            $itemId = $stackedItem['item']['id'] ?? null;
            if (empty($itemId)) {
                continue;
            }

            $this->stackedItems[$key]['item']['latest_quote_price'] = $this->getLatestQuotationPriceForItem($itemId, $this->cust_id);
            $this->stackedItems[$key]['item']['latest_quote_date'] = $this->getLatestQuotationDateForItem($itemId, $this->cust_id);

            if (($stackedItem['pricing_tier'] ?? '') === 'Previous Price') {
                $this->stackedItems[$key]['item_unit_price'] = $this->stackedItems[$key]['item']['latest_quote_price'] ?? 0;
                $qtyRaw = $this->stackedItems[$key]['item_qty'] ?? 0;
                $qty = ($qtyRaw === '' || $qtyRaw === null) ? 0.0 : floatval($qtyRaw);
                $this->stackedItems[$key]['amount'] = $qty * floatval($this->stackedItems[$key]['item_unit_price'] ?? 0);
            }
        }
        $this->recalculateTotals();

        $this->resetErrorBag(['cust_id']);
        if (method_exists($this, 'resetValidation')) {
            $this->resetValidation(['cust_id']);
        }
    }

    private function rowIndexOccupiedInStack(int $rowIndex): bool
    {
        foreach ($this->stackedItems as $stackedItem) {
            if ((int) ($stackedItem['original_row_index'] ?? -1) === $rowIndex) {
                return true;
            }
        }

        return isset($this->freeFormTextRows[$rowIndex]);
    }

    public function addItem($itemId, $rowIndex = null)
    {
        if ($this->isView) {
            return;
        }

        $item = Item::find($itemId);
        if (! $item) {
            toastr()->error('Item not found.');

            return;
        }

        $this->convertFreeFormTextToItems();

        if ($rowIndex === null) {
            $displayRow = $this->firstAvailableQuotationRowIndex();
            if ($displayRow === null) {
                toastr()->error('Maximum of '.$this->getQuotationGridRowCount().' rows allowed per quotation.');

                return;
            }
        } else {
            $displayRow = (int) $rowIndex;
        }

        [$rowToItemMap] = $this->buildQuotationRowMaps();
        if (isset($rowToItemMap[$displayRow]) || $this->isQuotationGridRowOccupied($displayRow) || $this->quotationRowHasPendingFreeForm($displayRow)) {
            toastr()->error('That row is already occupied. Choose an empty row.');

            return;
        }

        $anchorRow = $this->quotationAnchorRowForDisplayRow($displayRow);
        $this->stackedItems[] = $this->makeQuotationStackedItemFromInventory($item, $anchorRow);
        $this->recalculateTotals();
        $this->dispatch('focus-qty-row', ['rowIndex' => $displayRow]);
    }

    public function updatedRemark($value): void
    {
        $this->validateDescriptionLength();
    }

    public function validateDescriptionLength(): void
    {
        if ($this->isView) {
            return;
        }

        $this->lastValidRemark = $this->remark ?? '';
    }

    public function removeItem($index)
    {
        if ($this->isView) {
            return;
        }

        [, $itemToRowMap] = $this->buildQuotationRowMaps();
        $rowIndex = $itemToRowMap[$index] ?? null;

        if ($rowIndex === null) {
            return;
        }

        $this->removeQuotationRowAtGridIndex($rowIndex);
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
            if (preg_match('/stackedItems\\.(\\d+)\\.item_qty/', $prop, $m)) {
                $i = (int) $m[1];
                $qty = $this->stackedItems[$i]['item_qty'] ?? 0;
                if ($qty !== '' && $qty !== null) {
                    $this->stackedItems[$i]['item_qty'] = floatval($qty);
                }
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
            $qtyRaw = $item['item_qty'] ?? 0;
            $qty = ($qtyRaw === '' || $qtyRaw === null) ? 0.0 : floatval($qtyRaw);
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

        if (! $this->hasQuotationGridContent()) {
            toastr()->error('At least one item is required to save the revision');

            return;
        }

        $validationRules = [];
        foreach ($this->stackedItems as $index => $item) {
            if (! empty($item['is_text_only']) || empty($item['item']['id'])) {
                continue;
            }
            $validationRules["stackedItems.{$index}.item_qty"] = 'required|numeric|min:0.1';
            $validationRules["stackedItems.{$index}.item_unit_price"] = 'required|numeric|min:0';
        }

        $validator = Validator::make(['stackedItems' => $this->stackedItems], $validationRules);
        if ($validator->fails()) {
            $this->setErrorBag($validator->errors());

            return;
        }

        $this->pruneEmptyTextOnlyStackedItems();
        $this->normalizeQuotationDescriptions();
        $this->seedQuotationLastValidDescriptions();

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
                $this->persistQuotationStackedItems($q->id);
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
        if ($this->isView) {
            return;
        }

        $this->isPreviewMode = true;
        $saved = $this->saveDraft();
        $this->isPreviewMode = false;

        if ($saved !== true) {
            return;
        }

        if ($this->quotation && $this->quotation->id) {
            return redirect()->route('print.quotation.preview', $this->quotation->id);
        }
    }

    public function addQuotation()
    {
        if ($this->isView) {
            return false;
        }

        if (! $this->hasQuotationGridContent()) {
            toastr()->error('Please add at least one item or enter some text before saving.');

            return false;
        }

        $validationRules = [
            'quotation_num' => ['required', new UniqueInCurrentDatabase('quotations', 'quotation_num', $this->quotation?->id)],
            'cust_id' => ['required', new ExistsInCurrentDatabase('customers', 'id')],
            'salesman_id' => ['required', new ExistsInCurrentDatabase('users', 'id')],
            'date' => 'required|date',
        ];

        foreach ($this->stackedItems as $index => $item) {
            if (! empty($item['is_text_only']) || empty($item['item']['id'])) {
                continue;
            }
            $validationRules["stackedItems.{$index}.item_qty"] = 'required|numeric|min:0.1';
            $validationRules["stackedItems.{$index}.item_unit_price"] = 'required|numeric|min:0';
        }

        $validator = Validator::make([
            'quotation_num' => $this->quotation_num,
            'cust_id' => $this->cust_id,
            'salesman_id' => $this->salesman_id,
            'date' => $this->date,
            'stackedItems' => $this->stackedItems,
        ], $validationRules);

        if ($validator->fails()) {
            $this->setErrorBag($validator->errors());

            return false;
        }

        // enforce pricing tier when not manual (inventory items only)
        foreach ($this->stackedItems as $index => $item) {
            $isTextOnly = ! empty($item['is_text_only']) || empty($item['item']['id']);
            if ($isTextOnly) {
                continue;
            }
            if (!isset($item['price_manually_modified']) || !$item['price_manually_modified']) {
                if (empty($item['pricing_tier']) || !in_array($item['pricing_tier'], ['Customer Price', 'Term Price', 'Cash Price', 'Cost', 'Previous Price'])) {
                    $this->addError("stackedItems.{$index}.pricing_tier", 'Pricing tier required when not using custom price.');
                }
            }
        }
        if ($this->getErrorBag()->any()) {
            return false;
        }

        $this->pruneEmptyTextOnlyStackedItems();
        $this->normalizeQuotationDescriptions();
        $this->seedQuotationLastValidDescriptions();

        $connection = session('active_db') ?: DB::getDefaultConnection();

        try {
            DB::connection($connection)->beginTransaction();

            $this->recalculateTotals();

            // Create customer snapshot
            $customer = Customer::find($this->cust_id);
            $customerSnapshot = CustomerSnapshot::create([
                'customer_id' => $customer->id,
                'account' => $customer->account ?? '',
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

            } else {
                $this->quotation_num = QuotationNumberService::getNextQuotationNumber($connection, true);

                $this->quotation = Quotation::create([
                    'quotation_num' => $this->quotation_num,
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

            $this->persistQuotationStackedItems($this->quotation->id);

            DB::connection($connection)->commit();
            if (!$this->isPreviewMode) {
                toastr()->success('Quotation saved');
            }

            if (!$this->isPreviewMode) {
                if ($this->status === 'Save to Draft' && $this->quotation && $this->quotation->id) {
                    return redirect()->route('quotations.edit', $this->quotation->id);
                }

                return redirect()->to('/quotations');
            }

            return true;
        } catch (\Exception $e) {
            DB::connection($connection)->rollBack();
            if (!$this->isPreviewMode) {
                toastr()->error('Failed to save quotation: ' . $e->getMessage());
            }

            return false;
        }
    }

    public function render()
    {
        $this->date = $this->date ?? now()->toDateString();
        // Use current database connection (not just 'ups') to match validation
        $connection = session('active_db') ?: DB::getDefaultConnection();
        $this->salesmen = User::on($connection)->role('Salesperson')->orderBy('name','asc')->get();
        return view('livewire.quotation-form')->layout('layouts.app');
    }
}