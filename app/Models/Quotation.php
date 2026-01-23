<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ref_num',
        'cust_id',
        'salesman_id',
        'user_id',
        'updated_by',
        'date',
        'quotation_num',
        'total_amount',
        'remark',
        'customer_snapshot_id',
        'status',
        'printed',
    ];

    protected $casts = [
        'printed' => 'string',
    ];

    public function items()
    {
        return $this->hasMany(QuotationItem::class, 'quotation_id');
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

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
