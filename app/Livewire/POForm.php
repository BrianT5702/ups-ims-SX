<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\RestockList;
use App\Models\Transaction;
use App\Models\BatchTracking;
use App\Rules\UniqueInCurrentDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use App\Models\SupplierSnapshot;


#[Title('UR | Manage Purchase Order')]
class POForm extends Component
{
    public $isView = false;
    public $isEdit = false;
    public $purchaseOrder = null;
    public $stackedItems = [];
    public $suppliers;
    public $po_num;
    public $ref_num;
    public $supplier_id;
    public $selectedSupplier;
    public $date;
    public $remark;
    public $status = 'Pending Approval';
    public $itemSearchTerm = '';
    public $itemSearchResults = [];
    public $itemHighlightIndex = -1;
    public $supplierSearchTerm = '';
    public $supplierSearchResults = [];
    public $final_total_price = 0;
    public $tax_rate = 0;
    public $tax_amount = 0;
    public $grand_total = 0;
    public $total_price_line_item = [];
    public $source_doc_num;

    public $showBatchModal = false;
    public $currentItemIndex;
    public $batchNumber;
    public $isRevising = false;
    public $backupStackedItems = [];
    public $backupTaxRate = null;
    private bool $isPreviewMode = false;

    private function generateBatchNumber()
    {
        $date = now()->format('Ymd');
        $prefix = "BATCH-{$date}-";
        $latestBatch = BatchTracking::where('batch_num', 'like', $prefix . '%')
            ->orderBy('batch_num', 'desc')
            ->first();

        if ($latestBatch) {
            $lastNum = intval(substr($latestBatch->batch_num, strlen($prefix)));
            $nextNum = $lastNum + 1;
        } else {
            $nextNum = 1;
        }

        return $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    }

    public function openBatchModal($index)
    {
        $this->currentItemIndex = $index;
        $this->batchNumber = $this->generateBatchNumber();
        $this->showBatchModal = true;
    }


    public function mount(PurchaseOrder $purchaseOrder)
    {
        $this->isView = request()->routeIs('purchase-orders.view');
        $this->isEdit = request()->routeIs('purchase-orders.edit');
        $this->suppliers = Supplier::orderBy('sup_name', 'asc')->get();

        if ($purchaseOrder->id) {
            $this->purchaseOrder = $purchaseOrder;
            $this->po_num = $purchaseOrder->po_num;
            $this->ref_num = $purchaseOrder->ref_num;
            $this->selectedSupplier = $purchaseOrder->sup_id;
            $this->supplier_id = $purchaseOrder->sup_id;
            $this->date = $purchaseOrder->date;
            $this->remark = $purchaseOrder->remark;
            $this->status = $purchaseOrder->status;
            $this->final_total_price = $purchaseOrder->final_total_price;
            $this->tax_rate = $purchaseOrder->tax_rate ?? 0;
            $this->tax_amount = $purchaseOrder->tax_amount ?? 0;
            $this->grand_total = $purchaseOrder->grand_total ?? ($this->final_total_price + $this->tax_amount);

            // Load purchase order items
            foreach ($purchaseOrder->items as $poItem) {
                $this->stackedItems[] = [
                    'item' => [
                        'id' => $poItem->item->id,
                        'item_code' => $poItem->item->item_code,
                        'item_name' => $poItem->item->item_name,
                        'qty' => $poItem->item->qty,
                        'cost' => $poItem->item->cost,
                        'cust_price' => $poItem->item->cust_price,
                        'term_price' => $poItem->item->term_price,
                        'cash_price' => $poItem->item->cash_price,
                    ],
                    'item_qty' => $poItem->quantity,
                    'total_qty_received' => $poItem->total_qty_received,
                    'item_unit_price' => $poItem->unit_price,
                    'more_description' => $poItem->more_description,
                    'total_price_line_item' => $poItem->total_price_line_item,
                    'custom_item_name' => $poItem->custom_item_name ?? $poItem->item->item_name,
                ];
            }
        } else {
            $this->date = now()->toDateString();
            $this->po_num = 'PO' . time();

            $sessionItems = session('stackedItems');

            if (!empty($sessionItems)) {
                $restockItems = RestockList::with('item')->whereIn('id', $sessionItems)->get();

                foreach ($restockItems as $restockItem) {
                    $this->stackedItems[] = [
                        'item' => [
                            'id' => $restockItem->item->id,
                            'item_code' => $restockItem->item->item_code,
                            'item_name' => $restockItem->item->item_name,
                            'qty' => $restockItem->item->qty,
                            'cost' => $restockItem->item->cost,
                            'cust_price' => $restockItem->item->cust_price,
                            'term_price' => $restockItem->item->term_price,
                            'cash_price' => $restockItem->item->cash_price,
                        ],
                        'item_qty' => 1,
                        'item_unit_price' => 0.00,
                        'total_price_line_item' => 0.00,
                        'custom_item_name' => $restockItem->item->item_name,
                    ];
                }

                RestockList::whereIn('id', $sessionItems)->delete(); //remove items in restock-lists table
                session()->forget('stackedItems');
            }
        }

        

    }

    public function updatedItemSearchTerm()
    {
        if (!$this->isView) {
            $this->searchItems();
            $this->itemHighlightIndex = (count($this->itemSearchResults) > 0) ? 0 : -1;
        }
    }

    public function updatedSupplierSearchTerm()
    {
        if (!$this->isView) {
            $this->searchSuppliers();
        }
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

    public function searchSuppliers()
    {
        if (!empty($this->supplierSearchTerm)) {
            $this->supplierSearchResults = Supplier::where('sup_name', 'like', '%' . $this->supplierSearchTerm . '%')
                ->orWhere('account', 'like', '%' . $this->supplierSearchTerm . '%')
                ->orderBy('sup_name','asc')
                ->limit(10)
                ->get();
        } else {
            $this->supplierSearchResults = [];
        }
    }

    public function selectSupplier($supplierId)
    {
        if (!$this->isView) {
            $this->selectedSupplier = Supplier::find($supplierId);
            $this->supplier_id = $supplierId;
            $this->supplierSearchTerm = $this->selectedSupplier->sup_name;
            $this->supplierSearchResults = [];
        }
    }

    public function addItem($itemId)
    {
        if (!$this->isView) {
            $item = Item::find($itemId);

            if ($item) {
                $itemExists = false;

                foreach ($this->stackedItems as $key => $stackedItem) {
                    if ($stackedItem['item']['id'] === $item->id) {
                        $this->stackedItems[$key]['item_qty'] += 1;
                        $itemExists = true;
                        break;
                    }
                }

                if (!$itemExists) {
                    // Check if maximum items limit (15) is reached only for new items
                    if (count($this->stackedItems) >= 15) {
                        toastr()->error('Maximum of 15 items allowed per purchase order. Please remove some items before adding new ones.');
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
                        ],
                        'item_qty' => 1,
                        'item_unit_price' => 0.00,
                        'more_description' => null,
                        'total_price_line_item' => 0.00,
                        'custom_item_name' => $item->item_name,
                    ];
                }

                $this->itemSearchTerm = '';
                $this->itemSearchResults = [];
                $this->calculateTotalPrice();
            }
        }
    }

    public function removeItem($index)
    {
        if ($this->isView) {
            return;
        }

        // In Progress: only allow remove while revising AND when nothing received yet
        if ($this->status === 'In Progress' && !$this->isRevising) {
            return;
        }

        $received = intval($this->stackedItems[$index]['total_qty_received'] ?? 0);
        if ($received > 0) {
            toastr()->error('Cannot delete item that has received quantity');
            return;
        }

        unset($this->stackedItems[$index]);
        $this->stackedItems = array_values($this->stackedItems);
        $this->calculateTotalPrice();
    }

    public function toggleRevise()
    {
        if ($this->purchaseOrder && $this->status === 'In Progress') {
            if (!$this->isRevising) {
                // Entering revise mode: snapshot current values
                $this->backupStackedItems = $this->stackedItems;
                $this->backupTaxRate = $this->tax_rate;
                $this->isRevising = true;
            } else {
                // Cancelling revise: restore previous values and do not save
                if (!empty($this->backupStackedItems)) {
                    $this->stackedItems = $this->backupStackedItems;
                }
                if ($this->backupTaxRate !== null) {
                    $this->tax_rate = $this->backupTaxRate;
                }
                $this->calculateTotalPrice();
                $this->backupStackedItems = [];
                $this->backupTaxRate = null;
                $this->isRevising = false;
                toastr()->info('Revision cancelled');
            }
        }
    }

    public function saveRevision()
    {
        if (!($this->purchaseOrder && $this->status === 'In Progress' && $this->isRevising)) {
            return;
        }
        if (empty($this->stackedItems)) {
            toastr()->error('At least one item is required to save the revision');
            return;
        }
        $this->validate( [
            'stackedItems.*.item_qty' => 'required|integer|min:1',
            'stackedItems.*.item_unit_price' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $this->calculateTotalPrice();

            // Update PO totals
            $this->purchaseOrder->ref_num = $this->ref_num;
            $this->purchaseOrder->remark = $this->remark ?? null;
            $this->purchaseOrder->final_total_price = $this->final_total_price;
            $this->purchaseOrder->tax_rate = $this->tax_rate ?? null;
            $this->purchaseOrder->tax_amount = $this->tax_amount ?? null;
            $this->purchaseOrder->grand_total = $this->grand_total ?? null;
            $this->purchaseOrder->updated_by = auth()->id();
            $this->purchaseOrder->save();

            // Replace items
            PurchaseOrderItem::where('po_id', $this->purchaseOrder->id)->delete();
            foreach ($this->stackedItems as $item) {
                $qty = intval($item['item_qty'] ?? 0);
                $price = floatval($item['item_unit_price'] ?? 0);
                $lineTotal = $qty * $price;
                PurchaseOrderItem::create([
                    'po_id' => $this->purchaseOrder->id,
                    'item_id' => $item['item']['id'],
                    'custom_item_name' => $item['custom_item_name'] ?? null,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total_price_line_item' => $lineTotal,
                ]);
            }

            $this->isRevising = false;
            // Clear backups so Cancel won't revert after save
            $this->backupStackedItems = [];
            $this->backupTaxRate = null;
            toastr()->success('Revision saved');
        } catch (\Exception $e) {
            toastr()->error('Failed to save revision: ' . $e->getMessage());
        }
    }

    public function addPO()
    {
        if ($this->isView) {
            return;
        }
        $this->validate( [
            'po_num' => ['required', new UniqueInCurrentDatabase('purchase_orders', 'po_num', $this->purchaseOrder?->id)],
            'supplier_id' => $this->purchaseOrder ? 'nullable' : 'required|exists:suppliers,id', // Ignore if editing
            'date' => 'required|date',
            'stackedItems.*.item_qty' => 'required|integer|min:1',
            'stackedItems.*.item_unit_price' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
        ], [
            'po_num.required' => 'The PO num field is required .',
            'po_num.unique' => 'The PO num is already taken.',
            'supplier_id.required' => 'The supplier field is required.',
            'supplier_id.exists' => 'The selected supplier does not exist.',
            'date.required' => 'The date field is required.',
            'date.date' => 'The date must be a valid date.',
            'stackedItems.*.item_qty.required' => 'The item quantity is required for each item.',
            'stackedItems.*.item_qty.integer' => 'The item quantity must be an integer.',
            'stackedItems.*.item_qty.min' => 'The item quantity must be at least 1.',
            'stackedItems.*.item_unit_price.required' => 'The unit price is required for each item.',
            'stackedItems.*.item_unit_price.numeric' => 'The unit price must be a number.',
            'stackedItems.*.item_unit_price.min' => 'The unit price must be at least 0.',
            'tax_rate.numeric' => 'Tax rate must be a number.',
            'tax_rate.min' => 'Tax rate cannot be negative.',
            'tax_rate.max' => 'Tax rate cannot exceed 100%.',
        ]);
        try {
            $final_total_price = 0;

            foreach ($this->stackedItems as $item) {
                $item['item_qty'] = $item['item_qty'] ?? 0;
                $item['item_unit_price'] = $item['item_unit_price'] ?? 0;
                $total_price_line_item = $item['item_qty'] * $item['item_unit_price'];
                $final_total_price += $total_price_line_item;
            }

            $this->final_total_price = $final_total_price;
            $this->tax_amount = round(($this->tax_rate ?? 0) / 100 * $this->final_total_price, 2);
            $this->grand_total = round($this->final_total_price + $this->tax_amount, 2);

            if ($this->purchaseOrder) {
                // Update existing purchase order
                $this->purchaseOrder->po_num = $this->po_num;
                $this->purchaseOrder->ref_num = $this->ref_num;
                $this->purchaseOrder->sup_id = $this->supplier_id;
                $this->purchaseOrder->date = $this->date;
                $this->purchaseOrder->remark = $this->remark ?? null;
                $this->purchaseOrder->final_total_price = $this->final_total_price;
                $this->purchaseOrder->tax_rate = $this->tax_rate ?? null;
                $this->purchaseOrder->tax_amount = $this->tax_amount ?? null;
                $this->purchaseOrder->grand_total = $this->grand_total ?? null;
                if ($this->purchaseOrder->status === 'Rejected') {
                    $this->purchaseOrder->status = 'Pending Approval';
                    $this->status = 'Pending Approval';
                }
                $this->purchaseOrder->updated_by = auth()->id();
                $this->purchaseOrder->save();

                // Delete existing items
                PurchaseOrderItem::where('po_id', $this->purchaseOrder->id)->delete();

                // Create new items
                foreach ($this->stackedItems as $item) {
                    $item['item_qty'] = $item['item_qty'] ?? 0;
                    $item['item_unit_price'] = $item['item_unit_price'] ?? 0;
                    $total_price_line_item = $item['item_qty'] * $item['item_unit_price'];
                    PurchaseOrderItem::create([
                        'po_id' => $this->purchaseOrder->id,
                        'item_id' => $item['item']['id'],
                        'custom_item_name' => $item['custom_item_name'] ?? null,
                        'quantity' => $item['item_qty'],
                        'unit_price' => $item['item_unit_price'],
                        'more_description' => $item['more_description'] ?? null,
                        'total_price_line_item' => $total_price_line_item,
                    ]);
                }

                toastr()->success('PO updated successfully');
            } else {
                // Create supplier snapshot
                $supplier = Supplier::find($this->supplier_id);
                $supplierSnapshot = SupplierSnapshot::create([
                    'supplier_id' => $supplier->id,
                    'account' => $supplier->account,
                    'sup_name' => $supplier->sup_name,
                    'address_line1' => $supplier->address_line1,
                    'address_line2' => $supplier->address_line2,
                    'address_line3' => $supplier->address_line3,
                    'address_line4' => $supplier->address_line4,
                    'phone_num' => $supplier->phone_num,
                    'fax_num' => $supplier->fax_num,
                    'email' => $supplier->email,
                    'area' => $supplier->area,
                    'term' => $supplier->term,
                    'business_registration_no' => $supplier->business_registration_no,
                    'gst_registration_no' => $supplier->gst_registration_no,
                    'currency' => $supplier->currency,
                ]);

                // Create new Purchase Order
                $this->purchaseOrder = PurchaseOrder::create([
                    'po_num' => $this->po_num,
                    'ref_num' => $this->ref_num,
                    'sup_id' => $this->supplier_id,
                    'user_id' => auth()->id(),
                    'date' => $this->date,
                    'remark' => $this->remark ?? null,
                    'final_total_price' => $this->final_total_price,
                    'tax_rate' => $this->tax_rate ?? null,
                    'tax_amount' => $this->tax_amount ?? null,
                    'grand_total' => $this->grand_total ?? null,
                    'status' => $this->status,
                    'supplier_snapshot_id' => $supplierSnapshot->id,
                ]);

                foreach ($this->stackedItems as $item) {
                    $item['item_qty'] = $item['item_qty'] ?? 0;
                    $item['item_unit_price'] = $item['item_unit_price'] ?? 0;
                    $total_price_line_item = $item['item_qty'] * $item['item_unit_price'];
                    PurchaseOrderItem::create([
                        'po_id' => $this->purchaseOrder->id,
                        'item_id' => $item['item']['id'],
                        'custom_item_name' => $item['custom_item_name'] ?? null,
                        'quantity' => $item['item_qty'],
                        'unit_price' => $item['item_unit_price'],
                        'more_description' => $item['more_description'] ?? null,
                        'total_price_line_item' => $total_price_line_item,
                    ]);
                }

                toastr()->success('PO created successfully');
            }
        } catch (\Exception $e) {
            toastr()->error('An error occurred while processing the purchase order: ' . $e->getMessage());
        }

        // After save, stay on this PO instead of returning to list
        if ($this->purchaseOrder && $this->purchaseOrder->id) {
            return redirect()->to("/purchase-orders/{$this->purchaseOrder->id}/edit");
        }
        return redirect()->to('/purchase-orders');
    }

    public function saveDraft()
    {
        if ($this->isView) {
            return;
        }
        // Reuse same validation as addPO
        $this->validate( [
            'po_num' => ['required', new UniqueInCurrentDatabase('purchase_orders', 'po_num', $this->purchaseOrder?->id)],
            'supplier_id' => $this->purchaseOrder ? 'nullable' : 'required|exists:suppliers,id',
            'date' => 'required|date',
            'stackedItems.*.item_qty' => 'required|integer|min:1',
            'stackedItems.*.item_unit_price' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            // Recalculate totals
            $this->calculateTotalPrice();

            if ($this->purchaseOrder) {
                // Update existing
                $this->purchaseOrder->po_num = $this->po_num;
                $this->purchaseOrder->ref_num = $this->ref_num;
                $this->purchaseOrder->sup_id = $this->supplier_id;
                $this->purchaseOrder->date = $this->date;
                $this->purchaseOrder->remark = $this->remark ?? null;
                $this->purchaseOrder->final_total_price = $this->final_total_price;
                $this->purchaseOrder->tax_rate = $this->tax_rate ?? null;
                $this->purchaseOrder->tax_amount = $this->tax_amount ?? null;
                $this->purchaseOrder->grand_total = $this->grand_total ?? null;
                // Only change status to Save to Draft if not in preview mode
                if (!$this->isPreviewMode) {
                    $this->purchaseOrder->status = 'Save to Draft';
                    $this->status = 'Save to Draft';
                }
                $this->purchaseOrder->updated_by = auth()->id();
                $this->purchaseOrder->save();

                // Sync items
                PurchaseOrderItem::where('po_id', $this->purchaseOrder->id)->delete();
                foreach ($this->stackedItems as $item) {
                    $item['item_qty'] = $item['item_qty'] ?? 0;
                    $item['item_unit_price'] = $item['item_unit_price'] ?? 0;
                    $total_price_line_item = $item['item_qty'] * $item['item_unit_price'];
                    PurchaseOrderItem::create([
                        'po_id' => $this->purchaseOrder->id,
                        'item_id' => $item['item']['id'],
                        'custom_item_name' => $item['custom_item_name'] ?? null,
                        'quantity' => $item['item_qty'],
                        'unit_price' => $item['item_unit_price'],
                        'more_description' => $item['more_description'] ?? null,
                        'total_price_line_item' => $total_price_line_item,
                    ]);
                }

                if (!$this->isPreviewMode) {
                    toastr()->success('Draft saved');
                }
            } else {
                // Create supplier snapshot
                $supplier = Supplier::find($this->supplier_id);
                $supplierSnapshot = SupplierSnapshot::create([
                    'supplier_id' => $supplier->id,
                    'account' => $supplier->account,
                    'sup_name' => $supplier->sup_name,
                    'address_line1' => $supplier->address_line1,
                    'address_line2' => $supplier->address_line2,
                    'address_line3' => $supplier->address_line3,
                    'address_line4' => $supplier->address_line4,
                    'phone_num' => $supplier->phone_num,
                    'fax_num' => $supplier->fax_num,
                    'email' => $supplier->email,
                    'area' => $supplier->area,
                    'term' => $supplier->term,
                    'business_registration_no' => $supplier->business_registration_no,
                    'gst_registration_no' => $supplier->gst_registration_no,
                    'currency' => $supplier->currency,
                ]);

                // Create new PO as draft (Save to Draft)
                $this->purchaseOrder = PurchaseOrder::create([
                    'po_num' => $this->po_num,
                    'ref_num' => $this->ref_num,
                    'sup_id' => $this->supplier_id,
                    'user_id' => auth()->id(),
                    'date' => $this->date,
                    'remark' => $this->remark ?? null,
                    'final_total_price' => $this->final_total_price,
                    'tax_rate' => $this->tax_rate ?? null,
                    'tax_amount' => $this->tax_amount ?? null,
                    'grand_total' => $this->grand_total ?? null,
                    'status' => 'Save to Draft',
                    'supplier_snapshot_id' => $supplierSnapshot->id,
                ]);

                foreach ($this->stackedItems as $item) {
                    $item['item_qty'] = $item['item_qty'] ?? 0;
                    $item['item_unit_price'] = $item['item_unit_price'] ?? 0;
                    $total_price_line_item = $item['item_qty'] * $item['item_unit_price'];
                    PurchaseOrderItem::create([
                        'po_id' => $this->purchaseOrder->id,
                        'item_id' => $item['item']['id'],
                        'quantity' => $item['item_qty'],
                        'unit_price' => $item['item_unit_price'],
                        'more_description' => $item['more_description'] ?? null,
                        'total_price_line_item' => $total_price_line_item,
                    ]);
                }

                if (!$this->isPreviewMode) {
                    toastr()->success('Draft saved');
                }
            }

            // Redirect back to PO list after saving draft
            if (!$this->isPreviewMode) {
                return redirect()->to('/purchase-orders');
            }
        } catch (\Exception $e) {
            if (!$this->isPreviewMode) {
                toastr()->error('Failed to save draft: ' . $e->getMessage());
            }
        }
    }

    public function preview()
    {
        if ($this->isView) {
            return;
        }
        // Ensure we have a saved draft, then redirect to print preview
        $this->isPreviewMode = true;
        $this->saveDraft();
        $this->isPreviewMode = false;
        if ($this->purchaseOrder && $this->purchaseOrder->id) {
            return redirect()->route('print.purchase-order.preview', $this->purchaseOrder->id);
        }
    }

    public function receiveItems()
    {
        // ** Step 1: Pre-validation Phase **
        $validationErrors = [];
        $hasReceive = false;
        foreach ($this->stackedItems as $index => $item) {
            $receiveQty = intval($item['receive_qty'] ?? 0);
            $updateCost = floatval($item['update_cost'] ?? 0);
            $updateCustPrice = floatval($item['update_cust_price'] ?? 0);
            $updateTermPrice = floatval($item['update_term_price'] ?? 0);
            $updateCashPrice = floatval($item['update_cash_price'] ?? 0);
    
            // Get the PO item
            $poItem = PurchaseOrderItem::where('po_id', $this->purchaseOrder->id)
                ->where('item_id', $item['item']['id'])
                ->first();
    
            if (!$poItem) {
                $validationErrors[] = 'Purchase order item not found for item code: ' . $item['item']['item_code'];
                continue;
            }
    
            $itemRecord = Item::find($item['item']['id']);
    
            if ($receiveQty > 0) {
                $hasReceive = true;
                $newTotalReceived = ($poItem->total_qty_received ?? 0) + $receiveQty;
                if ($newTotalReceived > $poItem->quantity) {
                    $validationErrors[] = 'Total received quantity for item code ' . $item['item']['item_code'] . ' cannot exceed ordered quantity';
                    continue;
                }
            }
    
            if ($updateCost < 0) {
                $validationErrors[] = 'Cost for item code ' . $item['item']['item_code'] . ' cannot be negative';
                continue;
            }
    
            if ($updateCustPrice < 0) {
                $validationErrors[] = 'Customer price for item code ' . $item['item']['item_code'] . ' cannot be negative';
                continue;
            }
    
            if ($updateTermPrice < 0) {
                $validationErrors[] = 'Term price for item code ' . $item['item']['item_code'] . ' cannot be negative';
                continue;
            }
    
            if ($updateCashPrice < 0) {
                $validationErrors[] = 'Cash price for item code ' . $item['item']['item_code'] . ' cannot be negative';
                continue;
            }
        }
    
        if (!empty($validationErrors)) {
            foreach ($validationErrors as $error) {
                toastr()->error($error);
            }
            return;
        }
    
        // Generate batch number before starting transaction
        $batchNumber = $hasReceive ? $this->generateBatchNumber() : null;
    
        // ** Step 2: Update Phase **
        DB::beginTransaction();
        try {
            $hasUpdates = false;
            foreach ($this->stackedItems as $index => $item) {
                $receiveQty = intval($item['receive_qty'] ?? 0);
                $updateCost = floatval($item['update_cost'] ?? 0);
                $updateCustPrice = floatval($item['update_cust_price'] ?? 0);
                $updateTermPrice = floatval($item['update_term_price'] ?? 0);
                $updateCashPrice = floatval($item['update_cash_price'] ?? 0);
    
                $poItem = PurchaseOrderItem::where('po_id', $this->purchaseOrder->id)
                    ->where('item_id', $item['item']['id'])
                    ->first();
    
                $itemRecord = Item::find($item['item']['id']);
    
                if ($receiveQty > 0) {

                    $newBatch = BatchTracking::create([
                        'batch_num' => $batchNumber,
                        'po_id' => $this->purchaseOrder->id,
                        'item_id' => $item['item']['id'],
                        'quantity' => $receiveQty,
                        'received_date' => now(),
                        'received_by' => Auth::id()
                    ]);

            $qtyBefore = 0;

            $qtyAfter = $receiveQty;

            $newTotalReceived = ($poItem->total_qty_received ?? 0) + $receiveQty;
            $newQtyOnHand = $itemRecord->qty + $receiveQty;

            $itemRecord->qty = BatchTracking::where('item_id', $itemRecord->id)->sum('quantity');
            $poItem->total_qty_received = $newTotalReceived;
            $poItem->save();

            $this->stackedItems[$index]['total_qty_received'] = $newTotalReceived;
            $this->stackedItems[$index]['item']['qty'] = BatchTracking::where('item_id', $itemRecord->id)->sum('quantity');
            $this->stackedItems[$index]['receive_qty'] = null;
    
                    Transaction::create([
                        'item_id' => $itemRecord->id,
                        'qty_on_hand' => BatchTracking::where('item_id', $itemRecord->id)->sum('quantity'),
                        'qty_before' => $itemRecord->qty,
                        'qty_after' => $itemRecord->qty + $receiveQty,
                        'transaction_qty' => $receiveQty,
                        'transaction_type' => 'Stock In',
                        'user_id' => auth()->id(),
                        'source_type' => 'PO',
                        'source_doc_num' => $this->po_num,
                        'batch_id' => $newBatch->id,
                    ]);
    
                    $hasUpdates = true;
                }
    
                if ($updateCost > 0) {
                    $itemRecord->cost = $updateCost;
                    $this->stackedItems[$index]['update_cost'] = null;
                    $hasUpdates = true;
                }
    
                if ($updateCustPrice > 0) {
                    $itemRecord->cust_price = $updateCustPrice;
                    $this->stackedItems[$index]['update_cust_price'] = null;
                    $hasUpdates = true;
                }
    
                if ($updateTermPrice > 0) {
                    $itemRecord->term_price = $updateTermPrice;
                    $this->stackedItems[$index]['update_term_price'] = null;
                    $hasUpdates = true;
                }
    
                if ($updateCashPrice > 0) {
                    $itemRecord->cash_price = $updateCashPrice;
                    $this->stackedItems[$index]['update_cash_price'] = null;
                    $hasUpdates = true;
                }
    
                if ($hasUpdates) {
                    $this->stackedItems[$index]['item']['cost'] = $itemRecord->cost;
                    $this->stackedItems[$index]['item']['cust_price'] = $itemRecord->cust_price;
                    $this->stackedItems[$index]['item']['term_price'] = $itemRecord->term_price;
                    $this->stackedItems[$index]['item']['cash_price'] = $itemRecord->cash_price;
                    $itemRecord->save();
                }
            }
    
            if ($hasUpdates) {
                toastr()->success('Items updated successfully');
            }
    
            $allItemsReceived = PurchaseOrderItem::where('po_id', $this->purchaseOrder->id)
                ->where('quantity', '>', DB::raw('COALESCE(total_qty_received, 0)'))
                ->count() === 0;
    
            if ($allItemsReceived) {
                $this->purchaseOrder->status = 'Completed';
                $this->purchaseOrder->updated_by = auth()->id();
                $this->purchaseOrder->save();
                $this->status = 'Completed';
            }
    
            DB::commit();
            // Stay on this PO edit page
            return redirect()->to("/purchase-orders/{$this->purchaseOrder->id}/edit");
        } catch (\Exception $e) {
            DB::rollBack();
            toastr()->error('An error occurred while updating items: ' . $e->getMessage());
        }
    }
    

    public function updated($propertyName)
    {
        if ($this->isView) {
            return;
        }

        if (preg_match('/stackedItems\.\d+\.(item_qty|item_unit_price)/', $propertyName)) {
            if (preg_match('/stackedItems\.(\d+)\.item_qty/', $propertyName, $m)) {
                $i = (int)$m[1];
                $this->stackedItems[$i]['item_qty'] = intval($this->stackedItems[$i]['item_qty'] ?? 0);
            }
            if (preg_match('/stackedItems\.(\d+)\.item_unit_price/', $propertyName, $m2)) {
                $i2 = (int)$m2[1];
                $this->stackedItems[$i2]['item_unit_price'] = floatval($this->stackedItems[$i2]['item_unit_price'] ?? 0);
            }
            $this->calculateTotalPrice();
        }

        if ($propertyName === 'tax_rate') {
            $this->calculateTotalPrice();
        }
    }

    public function calculateTotalPrice()
    {
        $this->final_total_price = 0;
    
        foreach ($this->stackedItems as $key => $item) {
           
            $item_qty = intval($item['item_qty'] ?? 0);
            $item_unit_price = floatval($item['item_unit_price'] ?? 0);
            
            $total_price_line_item = $item_qty * $item_unit_price;
            $this->final_total_price += $total_price_line_item;
            $this->stackedItems[$key]['total_price_line_item'] = $total_price_line_item;
        }

        $this->tax_amount = round(($this->tax_rate ?? 0) / 100 * $this->final_total_price, 2);
        $this->grand_total = round($this->final_total_price + $this->tax_amount, 2);
    }

    public function changeStatus($newStatus)
    {
        try {
            // First save any changes
            $this->validate([
                'po_num' => ['required', new UniqueInCurrentDatabase('purchase_orders', 'po_num', $this->purchaseOrder?->id)],
                'supplier_id' => $this->purchaseOrder ? 'nullable' : 'required|exists:suppliers,id',
                'date' => 'required|date',
                'stackedItems.*.item_qty' => 'required|integer|min:1',
                'stackedItems.*.item_unit_price' => 'required|numeric|min:0',
                'tax_rate' => 'nullable|numeric|min:0|max:100',
            ]);

            // Recalculate totals
            $this->calculateTotalPrice();

            DB::beginTransaction();

            $purchaseOrder = PurchaseOrder::find($this->purchaseOrder->id);
            if ($purchaseOrder) {
                // Update PO details
                $purchaseOrder->po_num = $this->po_num;
                $purchaseOrder->ref_num = $this->ref_num;
                $purchaseOrder->sup_id = $this->supplier_id;
                $purchaseOrder->date = $this->date;
                $purchaseOrder->remark = $this->remark ?? null;
                $purchaseOrder->final_total_price = $this->final_total_price;
                $purchaseOrder->tax_rate = $this->tax_rate ?? null;
                $purchaseOrder->tax_amount = $this->tax_amount ?? null;
                $purchaseOrder->grand_total = $this->grand_total ?? null;

                // Update status
                if ($newStatus === 'Approved') {
                    $purchaseOrder->status = 'In Progress';
                    $this->status = 'In Progress';
                } else {
                    $purchaseOrder->status = $newStatus;
                    $this->status = $newStatus;
                }
                
                $purchaseOrder->updated_by = auth()->id();
                $purchaseOrder->save();

                // Update items
                PurchaseOrderItem::where('po_id', $this->purchaseOrder->id)->delete();
                foreach ($this->stackedItems as $item) {
                    $item['item_qty'] = $item['item_qty'] ?? 0;
                    $item['item_unit_price'] = $item['item_unit_price'] ?? 0;
                    $total_price_line_item = $item['item_qty'] * $item['item_unit_price'];
                    PurchaseOrderItem::create([
                        'po_id' => $this->purchaseOrder->id,
                        'item_id' => $item['item']['id'],
                        'custom_item_name' => $item['custom_item_name'] ?? null,
                        'quantity' => $item['item_qty'],
                        'unit_price' => $item['item_unit_price'],
                        'remark' => $item['remark'] ?? null,
                        'more_description' => $item['more_description'] ?? null,
                        'total_price_line_item' => $total_price_line_item,
                    ]);
                }

                DB::commit();
    
                $statusMessage = $newStatus === 'Approved' ? 'In Progress' : $newStatus;
                toastr()->success("PO status updated to $statusMessage successfully");
                return redirect()->to("/purchase-orders/{$purchaseOrder->id}/edit");
                
            } else {
                DB::rollBack();
                toastr()->error('Purchase order not found.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            toastr()->error('An error occurred while updating the status: ' . $e->getMessage());
        }
    }
    
    public function updatedStatus($value)
    {
        try {
            // Allow changing to Save to Draft from any status
            if ($value === 'Save to Draft') {
                $this->purchaseOrder->status = $value;
                $this->status = $value;
                $this->purchaseOrder->updated_by = auth()->id();
                $this->purchaseOrder->save();
                toastr()->success("Status updated to Save to Draft successfully");
                return;
            }

            // Only block reverting to Pending Approval from non-draft statuses
            if ($this->purchaseOrder->status !== 'Save to Draft' && 
                $this->purchaseOrder->status !== 'Pending Approval' && 
                $value === 'Pending Approval') {
                $this->status = $this->purchaseOrder->status;
                toastr()->error('Cannot revert to Pending Approval status once approved');
                return;
            }
    
            if ($this->purchaseOrder) {
                if ($value === 'Approved') {
                    $this->purchaseOrder->status = 'In Progress';
                    $this->status = 'In Progress';
                } else {
                    $this->purchaseOrder->status = $value;
                    $this->status = $value;
                }
                
                $this->purchaseOrder->updated_by = auth()->id();
                $this->purchaseOrder->save();
                
                $statusMessage = $value === 'Approved' ? 'In Progress' : $value;
                toastr()->success("Status updated to $statusMessage successfully");
            }
        } catch(\Exception $e) {
            toastr()->error('An error occurred while updating the status: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.po-form')->layout('layouts.app');
    }
}