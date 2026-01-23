<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\DeliveryOrder;
use App\Models\StockMovement;
use App\Models\CompanyProfile;
use PDF;
use App\Models\Quotation;
use App\Models\BatchTracking;
use App\Models\Transaction;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class PrintController extends Controller
{
    private function markPrinted($model)
    {
        $model->printed = 'Y';
        $model->save();
        return response()->json(['success' => true]);
    }
    public function previewPO($id)
    {
        $purchaseOrder = PurchaseOrder::with(['items.item', 'supplierSnapshot', 'user'])->findOrFail($id);
        $companyProfile = CompanyProfile::first();
        return view('purchase-orders.preview', compact('purchaseOrder', 'companyProfile'));
    }

    public function markPOPrinted($id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        return $this->markPrinted($purchaseOrder);
    }

    public function previewDO($id)
    {
        $deliveryOrder = DeliveryOrder::with(['items.item', 'customerSnapshot', 'user'])->findOrFail($id);
        // Order items by row_index to preserve absolute row positions (nulls last for backward compatibility)
        $deliveryOrder->setRelation('items', $deliveryOrder->items()->orderByRaw('row_index IS NULL, row_index')->get());
        $companyProfile = CompanyProfile::first();
        return view('delivery-orders.preview', compact('deliveryOrder', 'companyProfile'));
    }

    public function markDOPrinted($id)
    {
        $deliveryOrder = DeliveryOrder::findOrFail($id);
        return $this->markPrinted($deliveryOrder);
    }

    public function postDO($id)
    {
        try {
            $deliveryOrder = DeliveryOrder::with('items.item')->findOrFail($id);
            
            // Only post if status is not already "Completed"
            if ($deliveryOrder->status !== 'Completed') {
                DB::beginTransaction();
                
                // Change status to Completed
                $deliveryOrder->status = 'Completed';
                $deliveryOrder->updated_by = auth()->id();
                $deliveryOrder->save();
                
                // Deduct stock for all items (same logic as DOForm)
                foreach ($deliveryOrder->items as $doItem) {
                    // Skip text-only items
                    if (!$doItem->item_id) {
                        continue;
                    }
                    
                    $itemId = $doItem->item_id;
                    $qty = (int) ($doItem->qty ?? 0);
                    
                    if ($qty > 0) {
                        $this->deductFromBatchesFifo($itemId, $qty, $deliveryOrder->do_num);
                        
                        // Update item qty to reflect current batches
                        $itemRecord = Item::find($itemId);
                        if ($itemRecord) {
                            $itemRecord->qty = BatchTracking::where('item_id', $itemId)->sum('quantity');
                            $itemRecord->save();
                        }
                    }
                }
                
                DB::commit();
                return response()->json(['success' => true, 'message' => 'DO posted successfully']);
            }
            
            return response()->json(['success' => true, 'message' => 'DO already posted']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function deductFromBatchesFifo($itemId, $deductQty, $doNum)
    {
        // Get batches in FIFO order (oldest first)
        $batches = BatchTracking::where('item_id', $itemId)
            ->orderBy('received_date', 'asc')
            ->orderBy('id', 'asc')
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
                'source_type' => 'DO',
                'source_doc_num' => $doNum,
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
                'source_type' => 'DO',
                'source_doc_num' => $doNum,
                'batch_id' => $lastBatch->id,
                'created_at' => $baseTimestamp->copy()->subSeconds($batches->count() * 0.01),
                'updated_at' => $baseTimestamp->copy()->subSeconds($batches->count() * 0.01)
            ]);
        }
    }

    public function previewQuotation($id)
    {
        $quotation = Quotation::with(['items.item', 'customerSnapshot', 'user', 'salesman'])->findOrFail($id);
        $companyProfile = CompanyProfile::first();
        return view('quotations.preview', compact('quotation', 'companyProfile'));
    }

    public function markQuotationPrinted($id)
    {
        $quotation = Quotation::findOrFail($id);
        return $this->markPrinted($quotation);
    }

    public function previewStockMovement($id)
    {
        $stockMovement = StockMovement::with(['items.item', 'user'])->findOrFail($id);

        return view('stock-movements.preview', compact('stockMovement'));
    }

}
