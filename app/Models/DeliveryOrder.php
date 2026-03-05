<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\StealthModeScope;

class DeliveryOrder extends BaseModel
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
        'cust_id',
        'salesman_id',
        'user_id',
        'updated_by',
        'date',
        'cust_po',
        'do_num',
        'total_amount',
        'remark',
        // 'note',
        'customer_snapshot_id',
        'status',
        'printed',
    ];

    public function items()
    {
        return $this->hasMany(DeliveryOrderItem::class, 'do_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'cust_id');
    }

    public function customerSnapshot()
    {
        return $this->belongsTo(CustomerSnapshot::class);
    }

    public function salesman()
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'do_id');
    }

    /**
     * Generate the next DO number. Uses config/do.php. Start number is per-tenant:
     * UPS starts at 049037; URS/UCS start at 1. Increments by 1 for each new DO.
     */
    public static function getNextDoNumber(?string $connection = null): string
    {
        $prefix = config('do.prefix', 'DO');
        $padLength = (int) config('do.pad_length', 6);
        $tenants = config('do.tenants', []);
        $defaultStart = (int) config('do.default_start_number', 1);
        $startNumber = isset($tenants[$connection]) ? (int) $tenants[$connection] : $defaultStart;

        $query = $connection
            ? static::on($connection)->withoutStealthMode()
            : static::withoutStealthMode();

        $doNums = $query->withTrashed()->pluck('do_num');

        $maxNum = $doNums
            ->map(function ($doNum) use ($prefix) {
                if (str_starts_with($doNum, $prefix) && preg_match('/^' . preg_quote($prefix) . '(\d+)$/', $doNum, $m)) {
                    return (int) $m[1];
                }
                return null;
            })
            ->filter()
            ->max();

        $nextNum = $maxNum !== null ? $maxNum + 1 : $startNumber;

        return $prefix . str_pad((string) $nextNum, $padLength, '0', STR_PAD_LEFT);
    }
}
