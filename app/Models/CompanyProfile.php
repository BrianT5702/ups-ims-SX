<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanyProfile extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'company_profiles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_name', 
        'company_no',
        'gst_no',
        'address_line1', 
        'address_line2', 
        'address_line3', 
        'address_line4', 
        'phone_num1', 
        'phone_num2', 
        'fax_num', 
        'email'
    ];
}
