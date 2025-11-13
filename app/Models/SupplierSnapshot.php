<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SupplierSnapshot extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
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

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
} 