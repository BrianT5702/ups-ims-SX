<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuotationItem extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'quotation_id',
        'item_id',
        'custom_item_name',
        'qty',
        'unit_price',
        'pricing_tier',
        'amount',
        'more_description',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
