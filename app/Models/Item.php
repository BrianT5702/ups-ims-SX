<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends BaseModel
{
    use HasFactory;

    // The attributes that are mass assignable
    protected $fillable = [
        'item_code',
        'item_name',
        'um',
        'qty',    
        'cost',
        'cust_price',  
        'term_price',
        'cash_price',
        'stock_alert_level',
        'sup_id',
        'cat_id',
        'brand_id',
        'family_id',
        'group_id',
        'warehouse_id',
        'location_id',
        'image',
        'memo',
        'details',
    ];

    /**
     * Get the supplier associated with the item.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'sup_id');
    }

    /**
     * Get the category associated with the item.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id');
    }

    /**
     * Get the brand associated with the item.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * Get the family associated with the item.
     */
    public function family()
    {
        return $this->belongsTo(Family::class, 'family_id');
    }

    /**
     * Get the group associated with the item.
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class);
    }

    public function restockLists()
    {
        return $this->hasMany(RestockList::class, 'item_id');
    }

    public function deliveryOrderItems()
    {
        return $this->hasMany(DeliveryOrderItem::class);
    }
    
    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
