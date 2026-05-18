<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class BatchTracking extends BaseModel
{
    use HasFactory;

    /** Excel item-list import batch (see ItemImport). */
    public const IMPORT_BATCH_NUM = 'BATCH-00000000-000';

    protected $fillable = [
        'batch_num',
        'po_id',
        'item_id',
        'quantity',
        'original_quantity',
        'received_date',
        'received_by',
    ];

    protected $casts = [
        'quantity' => 'float',
        'original_quantity' => 'float',
    ];

    protected static function booted(): void
    {
        static::creating(function (BatchTracking $batch) {
            if ($batch->original_quantity === null && $batch->quantity !== null) {
                $batch->original_quantity = $batch->quantity;
            }
        });
    }

    /**
     * Opening stock from Excel import batch(es) for an item (never reduced by DO FIFO).
     */
    public static function importOpeningQuantityForItem(int $itemId): float
    {
        $rows = static::query()
            ->where('item_id', $itemId)
            ->where('batch_num', self::IMPORT_BATCH_NUM)
            ->get();

        if ($rows->isEmpty()) {
            return 0.0;
        }

        return max(0, (float) $rows->sum(
            fn (BatchTracking $row) => $row->original_quantity ?? $row->quantity ?? 0
        ));
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}