<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Scopes\StealthModeScope;

class Transaction extends BaseModel
{
    use HasFactory;

    protected $table = 'transactions';

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new StealthModeScope());
    }

    /**
     * Get transactions without stealth mode scope (for Super Admin or system operations)
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function withoutStealthMode()
    {
        return static::withoutGlobalScope(StealthModeScope::class);
    }

    protected $fillable = [
        'item_id',
        'qty_on_hand',
        'qty_before',
        'qty_after',
        'transaction_qty',
        'transaction_type',
        'source_type',
        'source_doc_num',
        'user_id',
        'batch_id',
    ];

    public $timestamps = true;

    public function batch()
    {
        return $this->belongsTo(BatchTracking::class, 'batch_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'source_doc_num', 'po_num');
    }

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'source_doc_num', 'do_num');
    }

    /** @return list<string> */
    public static function logDocDateDoSourceTypes(): array
    {
        return ['DO', 'Delivery Order', 'DO Reversal', 'DO Status Reversal', 'DO Delta Reversal', 'DO Draft Delta'];
    }

    /** @return list<string> */
    public static function logDocDatePoSourceTypes(): array
    {
        return ['PO', 'Purchase Order', 'PO Reversal'];
    }

    /**
     * Tie-break for transaction log when sorted by document date **DESC** (newest at top).
     * Same calendar day is shown reverse-chronologically: **later** events nearer the top.
     * Business rule “DO before PO” in time ⇒ **PO (receipt) above DO (shipment)** in this list,
     * so **Stock In before Stock Out** on equal doc date.
     */
    public static function logListTieBreakTransactionTypeAscSql(): string
    {
        return "CASE WHEN transactions.transaction_type = 'Stock In' THEN 0 WHEN transactions.transaction_type = 'Stock Out' THEN 1 ELSE 2 END";
    }

    /**
     * Second tie-break for DESC list: **PO-linked row before DO-linked** on same doc date.
     */
    public static function logListTieBreakDocFamilyAscSql(): string
    {
        return 'CASE WHEN tx_log_po.id IS NOT NULL THEN 0 WHEN tx_log_do.id IS NOT NULL THEN 1 ELSE 2 END';
    }

    /**
     * Tie-break for ledger walk ordered by doc date **ASC** (chronological): DO (Out) before PO (In) same day.
     */
    public static function logLedgerTieBreakTransactionTypeAscSql(): string
    {
        return "CASE WHEN transactions.transaction_type = 'Stock Out' THEN 0 WHEN transactions.transaction_type = 'Stock In' THEN 1 ELSE 2 END";
    }

    /**
     * Second tie-break for ledger ASC: **DO-linked before PO-linked** on same doc date.
     */
    public static function logLedgerTieBreakDocFamilyAscSql(): string
    {
        return 'CASE WHEN tx_log_do.id IS NOT NULL THEN 0 WHEN tx_log_po.id IS NOT NULL THEN 1 ELSE 2 END';
    }

    /**
     * Left joins used to sort/filter the transaction log by DO/PO document date.
     */
    public function scopeWithLogDocDateJoins(Builder $query): Builder
    {
        $query->select('transactions.*');

        $query->leftJoin('delivery_orders as tx_log_do', function ($join) {
            $join->on('transactions.source_doc_num', '=', 'tx_log_do.do_num')
                ->whereIn('transactions.source_type', self::logDocDateDoSourceTypes())
                ->whereNull('tx_log_do.deleted_at');
        });

        $query->leftJoin('purchase_orders as tx_log_po', function ($join) {
            $join->on('transactions.source_doc_num', '=', 'tx_log_po.po_num')
                ->whereIn('transactions.source_type', self::logDocDatePoSourceTypes())
                ->whereNull('tx_log_po.deleted_at');
        });

        return $query;
    }

    public function scopeWhereLogDisplayDateBetween(Builder $query, Carbon $start, Carbon $end): Builder
    {
        $query->whereRaw(
            'COALESCE(tx_log_do.date, tx_log_po.date, transactions.created_at) BETWEEN ? AND ?',
            [$start, $end]
        );

        return $query;
    }

    public function scopeWhereLogDisplayDateOnOrBefore(Builder $query, Carbon $moment): Builder
    {
        $query->whereRaw(
            'COALESCE(tx_log_do.date, tx_log_po.date, transactions.created_at) <= ?',
            [$moment]
        );

        return $query;
    }

    public function scopeOrderByLogDisplayDate(Builder $query, string $direction = 'desc'): Builder
    {
        $dir = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $query->orderByRaw("COALESCE(tx_log_do.date, tx_log_po.date, transactions.created_at) {$dir}");

        if ($dir === 'DESC') {
            $query->orderByRaw(self::logListTieBreakTransactionTypeAscSql() . ' ASC')
                ->orderByRaw(self::logListTieBreakDocFamilyAscSql() . ' ASC');
        } else {
            $query->orderByRaw(self::logLedgerTieBreakTransactionTypeAscSql() . ' ASC')
                ->orderByRaw(self::logLedgerTieBreakDocFamilyAscSql() . ' ASC');
        }

        $query->orderBy('transactions.id', $dir);

        return $query;
    }

    /**
     * Date for transaction log display: document date when tied to a DO/PO, otherwise posting time.
     */
    public function logDisplayDate(): Carbon
    {
        $docNum = $this->source_doc_num;
        if (!$docNum || $docNum === '-') {
            return $this->created_at;
        }

        if (in_array($this->source_type, self::logDocDateDoSourceTypes(), true)) {
            $do = $this->relationLoaded('deliveryOrder')
                ? $this->deliveryOrder
                : $this->deliveryOrder()->first();
            if ($do && $do->date) {
                return Carbon::parse($do->date)->startOfDay();
            }
        }

        if (in_array($this->source_type, self::logDocDatePoSourceTypes(), true)) {
            $po = $this->relationLoaded('purchaseOrder')
                ? $this->purchaseOrder
                : $this->purchaseOrder()->first();
            if ($po && $po->date) {
                return Carbon::parse($po->date)->startOfDay();
            }
        }

        return $this->created_at;
    }
}
