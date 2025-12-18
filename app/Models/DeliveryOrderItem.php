<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryOrderItem extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'delivery_orders_items';

    protected $fillable = [
        'do_id',
        'item_id',
        'custom_item_name',
        'qty',
        'unit_price',
        'pricing_tier',
        'amount',
        'more_description',
    ];

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'do_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id')->withDefault();
    }
}
