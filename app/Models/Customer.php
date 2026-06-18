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
        return $this->belongsTo(CompanyUser::class, 'salesman_id');
    }

    /**
     * Name or account substring match, ordered for autocomplete: prefix on account,
     * prefix on name, then substring matches (not "best match" by position within name).
     */
    public function scopeAutocompleteSearch($query, string $term)
    {
        $term = trim($term);
        if ($term === '') {
            return $query->whereRaw('1 = 0');
        }

        $escaped = addcslashes($term, '%_\\');
        $contains = '%' . $escaped . '%';
        $prefix = $escaped . '%';

        return $query
            ->where(function ($q) use ($contains) {
                $q->where('cust_name', 'like', $contains)
                    ->orWhere('account', 'like', $contains);
            })
            ->orderByRaw(
                '(CASE
                    WHEN `account` LIKE ? THEN 1
                    WHEN `cust_name` LIKE ? THEN 2
                    WHEN `account` LIKE ? THEN 3
                    WHEN `cust_name` LIKE ? THEN 4
                    ELSE 5 END)',
                [$prefix, $prefix, $contains, $contains]
            )
            ->orderBy('account')
            ->orderBy('cust_name');
    }
}
