<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryOrder extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ref_num',
        'cust_id',
        'salesman_id',
        'user_id',
        'date',
        'cust_po',
        'do_num',
        'total_amount',
        'remark',
        // 'note',
        'customer_snapshot_id',
        'status',
        'printed',
    ];

    public function items()
    {
        return $this->hasMany(DeliveryOrderItem::class, 'do_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'cust_id');
    }

    public function customerSnapshot()
    {
        return $this->belongsTo(CustomerSnapshot::class);
    }

    public function salesman()
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'do_id');
    }
}
