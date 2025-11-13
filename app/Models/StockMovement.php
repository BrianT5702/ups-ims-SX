<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockMovement extends BaseModel
{
    protected $fillable = [
        'movement_type',
        'movement_date',
        'user_id',
        'reference_no',
        'remarks',
    ];

    protected $casts = [
        'movement_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockMovementItem::class);
    }
}
