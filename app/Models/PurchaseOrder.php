<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\StealthModeScope;

class PurchaseOrder extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::addGlobalScope(new StealthModeScope());
    }

    /**
     * Query without stealth mode scope (for Super Admin or system use).
     */
    public static function withoutStealthMode()
    {
        return static::withoutGlobalScope(StealthModeScope::class);
    }

    protected $fillable = [
        'ref_num',
        'po_num',
        'sup_id',
        'user_id',
        'updated_by',
        'date',
        'remark',
        'status',
        'printed',
        'final_total_price',
        'tax_rate',
        'tax_amount',
        'grand_total',
        'supplier_snapshot_id',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'sup_id');
    }

    public function supplierSnapshot()
    {
        return $this->belongsTo(SupplierSnapshot::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'po_id');
    }

    

    
}
