<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends BaseModel
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'item_id',
        'qty_on_hand',
        'qty_before',
        'qty_after',
        'transaction_qty',
        'transaction_type',
        'source_type',
        'source_doc_num',
        'user_id',
        'batch_id',
    ];

    public $timestamps = true;

    public function batch()
    {
        return $this->belongsTo(BatchTracking::class, 'batch_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'source_doc_num');
    }

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'source_doc_num');
    }
}
