<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class RestockList extends BaseModel
{
    use HasFactory;

    // Table associated with the model
    protected $table = 'restock_lists';

    // Mass assignable attributes
    protected $fillable = [
        'item_id',
    ];

    /**
     * Relationship to the Item model.
     * Each restock list entry belongs to one item.
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id')->select(['id', 'item_name', 'item_code', 'qty']);
    }

}
