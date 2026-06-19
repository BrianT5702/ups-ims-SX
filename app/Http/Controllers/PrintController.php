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
use App\Support\TenantDatabase;
use Illuminate\Support\Facades\DB;

class PrintController extends Controller
{
    private function bootTenantFromRequest(Request $request): string
    {
        return TenantDatabase::resolveAndApply($request);
    }

    private function markPrinted($model)
    {
        $model->printed = 'Y';
        $model->save();
        return response()->json(['success' => true]);
    }
    public function previewPO(Request $request, $id)
    {
        $connection = $this->bootTenantFromRequest($request);
        $purchaseOrder = PurchaseOrder::on($connection)->with(['items.item', 'supplierSnapshot', 'user'])->findOrFail($id);
        $companyProfile = CompanyProfile::on($connection)->first();
        return view('purchase-orders.preview', compact('purchaseOrder', 'companyProfile', 'connection'));
    }

    public function markPOPrinted(Request $request, $id)
    {
        $connection = $this->bootTenantFromRequest($request);
        $purchaseOrder = PurchaseOrder::on($connection)->findOrFail($id);
        return $this->markPrinted($purchaseOrder);
    }

    public function previewDO(Request $request, $id)
    {
        $connection = $this->bootTenantFromRequest($request);
        $deliveryOrder = DeliveryOrder::on($connection)->with(['items.item', 'customerSnapshot', 'user'])->findOrFail($id);
        // Order items by row_index to preserve absolute row positions (nulls last for backward compatibility)
        $deliveryOrder->setRelation('items', $deliveryOrder->items()->orderByRaw('row_index IS NULL, row_index')->get());
        $companyProfile = CompanyProfile::on($connection)->first();
        return view('delivery-orders.preview', compact('deliveryOrder', 'companyProfile', 'connection'));
    }

    public function markDOPrinted(Request $request, $id)
    {
        $connection = $this->bootTenantFromRequest($request);
        $deliveryOrder = DeliveryOrder::on($connection)->findOrFail($id);
        return $this->markPrinted($deliveryOrder);
    }

    public function postDO(Request $request, $id)
    {
        try {
            $connection = $this->bootTenantFromRequest($request);
            $deliveryOrder = DeliveryOrder::on($connection)->with('items.item')->findOrFail($id);
            
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
                    $qty = round((float) ($doItem->qty ?? 0), 2);
                    
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

    private function deductFromBatchesFifo($itemId, float $deductQty, $doNum)
    {
        $deductQty = round($deductQty, 2);
        if ($deductQty <= 0) {
            return;
        }

        // Get batches: positive-quantity first (so FIFO prefers real received
        // stock over old empty placeholders like AUTO-...), then within each
        // tier oldest-first by received_date / id.
        $batches = BatchTracking::where('item_id', $itemId)
            ->orderByRaw('CASE WHEN quantity > 0 THEN 0 ELSE 1 END')
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

        $currentQtyOnHand = (float) BatchTracking::where('item_id', $itemId)->sum('quantity');
        $baseTimestamp = now();
        $remainingDeductQty = $deductQty;
        $txIndex = 0;

        $positiveBatches = $batches->filter(fn ($batch) => (float) $batch->quantity > 0);

        if ($positiveBatches->isEmpty()) {
            $batch = $batches->last();
            if (!$batch) {
                $batch = BatchTracking::create([
                    'batch_num' => 'AUTO-' . now()->format('YmdHis'),
                    'item_id' => $itemId,
                    'quantity' => 0,
                    'received_date' => now(),
                    'received_by' => auth()->id(),
                ]);
            }

            $qtyBefore = $currentQtyOnHand;
            $batch->quantity = (float) $batch->quantity - $remainingDeductQty;
            $batch->save();
            $qtyAfter = (float) BatchTracking::where('item_id', $itemId)->sum('quantity');

            Transaction::create([
                'item_id' => $itemId,
                'qty_on_hand' => $qtyAfter,
                'qty_before' => $qtyBefore,
                'qty_after' => $qtyAfter,
                'transaction_qty' => $remainingDeductQty,
                'transaction_type' => 'Stock Out',
                'user_id' => auth()->id(),
                'source_type' => 'DO',
                'source_doc_num' => $doNum,
                'batch_id' => $batch->id,
                'created_at' => $baseTimestamp->copy()->subSeconds($txIndex * 0.01),
                'updated_at' => $baseTimestamp->copy()->subSeconds($txIndex * 0.01),
            ]);

            return;
        }

        foreach ($positiveBatches as $batch) {
            if ($remainingDeductQty <= 0) {
                break;
            }

            $take = min($remainingDeductQty, (float) $batch->quantity);
            if ($take <= 0) {
                continue;
            }

            $qtyBefore = $currentQtyOnHand;
            $batch->quantity = (float) $batch->quantity - $take;
            $batch->save();
            $currentQtyOnHand -= $take;
            $remainingDeductQty = round($remainingDeductQty - $take, 2);

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
                'created_at' => $baseTimestamp->copy()->subSeconds($txIndex * 0.01),
                'updated_at' => $baseTimestamp->copy()->subSeconds($txIndex * 0.01),
            ]);
            $txIndex++;
        }

        if ($remainingDeductQty > 0) {
            $lastBatch = $batches->last();
            if (!$lastBatch) {
                return;
            }

            $qtyBefore = $currentQtyOnHand;
            $lastBatch->quantity = (float) $lastBatch->quantity - $remainingDeductQty;
            $lastBatch->save();
            $qtyAfter = (float) BatchTracking::where('item_id', $itemId)->sum('quantity');

            Transaction::create([
                'item_id' => $itemId,
                'qty_on_hand' => $qtyAfter,
                'qty_before' => $qtyBefore,
                'qty_after' => $qtyAfter,
                'transaction_qty' => $remainingDeductQty,
                'transaction_type' => 'Stock Out',
                'user_id' => auth()->id(),
                'source_type' => 'DO',
                'source_doc_num' => $doNum,
                'batch_id' => $lastBatch->id,
                'created_at' => $baseTimestamp->copy()->subSeconds($txIndex * 0.01),
                'updated_at' => $baseTimestamp->copy()->subSeconds($txIndex * 0.01),
            ]);
        }
    }

    public function previewQuotation(Request $request, $id)
    {
        $connection = $this->bootTenantFromRequest($request);
        $quotation = Quotation::on($connection)->with(['items.item', 'customerSnapshot', 'user', 'salesman'])->findOrFail($id);
        $quotation->setRelation(
            'items',
            $quotation->items()->orderByRaw('row_index IS NULL, row_index')->orderBy('id')->get()
        );
        $companyProfile = CompanyProfile::on($connection)->first();
        return view('quotations.preview', compact('quotation', 'companyProfile', 'connection'));
    }

    public function markQuotationPrinted(Request $request, $id)
    {
        $connection = $this->bootTenantFromRequest($request);
        $quotation = Quotation::on($connection)->findOrFail($id);
        return $this->markPrinted($quotation);
    }

    public function previewStockMovement($id)
    {
        $stockMovement = StockMovement::with(['items.item', 'user'])->findOrFail($id);

        return view('stock-movements.preview', compact('stockMovement'));
    }

}
