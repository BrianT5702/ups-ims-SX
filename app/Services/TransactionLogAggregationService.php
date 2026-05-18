<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Collection;

/**
 * Mirrors transaction-log visibility so report IN/OUT match what users see in the log.
 */
class TransactionLogAggregationService
{
    /** @var array<string, array{visible_ids: array<int,int>, event_qty: array<int,float>, is_latest: array<int,bool>}> */
    private array $doEventsCache = [];

    public function getDoSourceTypes(): array
    {
        return ['DO', 'Delivery Order'];
    }

    public function getDoReversalSourceTypes(): array
    {
        return ['DO Reversal', 'DO Status Reversal', 'DO Delta Reversal', 'DO Draft Delta'];
    }

    /**
     * @param  array<int>  $itemIds
     * @return array{visible_ids: array<int,int>, event_qty: array<int,float>, is_latest: array<int,bool>}
     */
    public function buildDoStockOutEvents(array $itemIds): array
    {
        sort($itemIds);
        $cacheKey = implode(',', $itemIds);

        if (isset($this->doEventsCache[$cacheKey])) {
            return $this->doEventsCache[$cacheKey];
        }

        $doSourceTypes = $this->getDoSourceTypes();
        $reversalTypes = $this->getDoReversalSourceTypes();

        $query = Transaction::query()
            ->whereIn('source_type', array_merge($doSourceTypes, $reversalTypes));

        if ($itemIds !== []) {
            $query->whereIn('item_id', $itemIds);
        }

        $rows = $query
            ->orderBy('source_doc_num')
            ->orderBy('item_id')
            ->orderBy('id')
            ->get(['id', 'source_doc_num', 'item_id', 'source_type', 'transaction_type', 'transaction_qty']);

        $visibleIds = [];
        $eventQty = [];
        $isLatest = [];

        $byPair = $rows->groupBy(
            fn ($r) => trim((string) ($r->source_doc_num ?? '')) . '|' . (int) ($r->item_id ?? 0)
        );

        foreach ($byPair as $pairRows) {
            $events = [];
            $current = null;

            foreach ($pairRows as $row) {
                $isOut = in_array($row->source_type, $doSourceTypes, true)
                    && $row->transaction_type === 'Stock Out';
                $isReversalIn = in_array($row->source_type, $reversalTypes, true)
                    && $row->transaction_type === 'Stock In';

                if ($isOut) {
                    if ($current === null) {
                        $current = ['ids' => [], 'qty' => 0.0];
                    }
                    $current['ids'][] = (int) $row->id;
                    $current['qty'] += abs((float) $row->transaction_qty);
                } elseif ($isReversalIn && $current !== null) {
                    $events[] = $current;
                    $current = null;
                }
            }

            if ($current !== null) {
                $events[] = $current;
            }

            $eventCount = count($events);
            foreach ($events as $idx => $ev) {
                $repId = max($ev['ids']);
                $visibleIds[] = $repId;
                $eventQty[$repId] = $ev['qty'];
                $isLatest[$repId] = ($idx === $eventCount - 1);
            }
        }

        return $this->doEventsCache[$cacheKey] = [
            'visible_ids' => $visibleIds,
            'event_qty' => $eventQty,
            'is_latest' => $isLatest,
        ];
    }

    /**
     * Quantity to add to report IN or OUT for one ledger row (log-aligned).
     */
    public function reportMovementQuantity(Transaction $transaction, array $doEvents): float
    {
        $reversalTypes = $this->getDoReversalSourceTypes();
        $doSourceTypes = $this->getDoSourceTypes();

        if (in_array($transaction->source_type, $reversalTypes, true)) {
            return 0.0;
        }

        if (in_array($transaction->source_type, $doSourceTypes, true)
            && $transaction->transaction_type === 'Stock Out'
        ) {
            $id = (int) $transaction->id;
            $isLatest = $doEvents['is_latest'] ?? [];

            if (! ($isLatest[$id] ?? false)) {
                return 0.0;
            }

            return (float) ($doEvents['event_qty'][$id] ?? abs((float) $transaction->transaction_qty));
        }

        return abs((float) $transaction->transaction_qty);
    }

    /**
     * @param  Collection<int, Transaction>  $transactions
     * @return array{in: float, out: float}
     */
    public function sumPeriodInOut(Collection $transactions): array
    {
        if ($transactions->isEmpty()) {
            return ['in' => 0.0, 'out' => 0.0];
        }

        $itemIds = $transactions->pluck('item_id')->filter()->unique()->map(fn ($id) => (int) $id)->values()->all();
        $doEvents = $this->buildDoStockOutEvents($itemIds);

        $in = 0.0;
        $out = 0.0;

        foreach ($transactions as $transaction) {
            $qty = $this->reportMovementQuantity($transaction, $doEvents);

            if ($qty <= 0) {
                continue;
            }

            if ($transaction->transaction_type === 'Stock In') {
                $in += $qty;
            } elseif ($transaction->transaction_type === 'Stock Out') {
                $out += $qty;
            }
        }

        return ['in' => $in, 'out' => $out];
    }
}
