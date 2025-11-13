<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerSnapshot extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'account',
        'cust_name',
        'address_line1',
        'address_line2',
        'address_line3',
        'address_line4',
        'phone_num',
        'fax_num',
        'email',
        'area',
        'term',
        'business_registration_no',
        'gst_registration_no',
        'pricing_tier',
        'currency',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
} 