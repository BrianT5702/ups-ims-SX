<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Scopes\StealthModeScope;

class Transaction extends BaseModel
{
    use HasFactory;

    protected $table = 'transactions';

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new StealthModeScope());
    }

    /**
     * Get transactions without stealth mode scope (for Super Admin or system operations)
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function withoutStealthMode()
    {
        return static::withoutGlobalScope(StealthModeScope::class);
    }

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
        return $this->belongsTo(PurchaseOrder::class, 'source_doc_num', 'po_num');
    }

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'source_doc_num', 'do_num');
    }
}
