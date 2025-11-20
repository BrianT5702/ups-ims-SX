<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        // 'cust_name',
        // 'contact_person',
        // 'address_line1',
        // 'address_line2',
        // 'address_line3',
        // 'address_line4',
        // 'phone_num1',
        // 'phone_num2',
        // 'fax_num',
        // 'email',
        // 'pricing_tier',
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
        'salesman_id',
        'currency',
        'pricing_tier',
    ];

    public function deliveryOrders()
    {
        return $this->hasMany(DeliveryOrder::class, 'cust_id');
    }

    public function salesman()
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }
    
}
