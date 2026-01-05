<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\DeliveryOrder;
use App\Models\StockMovement;
use App\Models\CompanyProfile;
use PDF;
use App\Models\Quotation;

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
