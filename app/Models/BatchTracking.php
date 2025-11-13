<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class BatchTracking extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'batch_num',
        'po_id',
        'item_id',
        'quantity',
        'received_date',
        'received_by'
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}