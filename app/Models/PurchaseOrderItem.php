<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderItem extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'po_id',
        'item_id',
        'custom_item_name',
        'unit_price',
        'quantity',
        'total_qty_received',
        'total_price_line_item',
        'more_description',
    ];

    protected $casts = [
        'quantity' => 'float',
        'total_qty_received' => 'float',
        'unit_price' => 'float',
        'total_price_line_item' => 'float',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
