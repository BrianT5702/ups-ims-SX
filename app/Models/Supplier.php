<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        // 'sup_name',
        // 'contact_person',
        // 'address_line1',
        // 'address_line2',
        // 'address_line3',
        // 'address_line4',
        // 'phone_num1',
        // 'phone_num2',
        // 'fax_num',
        // 'email',
        'account',
        'sup_name',
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
        'currency',
    ];

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'sup_id');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'sup_id');
    }
}
