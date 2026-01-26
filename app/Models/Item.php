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

    /**
     * Fix encoding issues - replace "?" with "°" when it appears after numbers (common degree symbol issue)
     */
    private function fixDegreeSymbol($text)
    {
        if (empty($text)) {
            return $text;
        }
        // Replace patterns like "90?" with "90°" (number followed by question mark)
        return preg_replace('/(\d+)\?/', '$1°', $text);
    }

    /**
     * Accessor to fix degree symbols in item_name
     */
    public function getItemNameAttribute($value)
    {
        $rawValue = $this->attributes['item_name'] ?? $value;
        return $this->fixDegreeSymbol($rawValue);
    }

    /**
     * Accessor to fix degree symbols in details
     */
    public function getDetailsAttribute($value)
    {
        $rawValue = $this->attributes['details'] ?? $value;
        return $this->fixDegreeSymbol($rawValue);
    }
}
