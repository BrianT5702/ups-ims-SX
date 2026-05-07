<?php

namespace App\Jobs;

use App\Models\CompanyProfile;
use App\Models\Item;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateInventoryPdfReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(
        public string $token,
        public array $selectedColumns,
        public string $stockFilter,
        public ?int $selectedGroupId,
        public ?int $selectedFamilyId,
        public ?int $selectedCategoryId,
        public bool $showGrouping,
        public ?string $databaseConnection = null,
    ) {
    }

    public function handle(): void
    {
        $cacheKey = $this->cacheKey($this->token);
        $originalMemoryLimit = ini_get('memory_limit');
        $dbConn = $this->resolveDatabaseConnection();

        try {
            Cache::put($cacheKey, [
                'status' => 'processing',
                'message' => 'Preparing PDF data...',
                'progress' => 20,
            ], now()->addHours(2));

            $finalColumns = array_unique(array_merge(['item_code', 'item_name'], $this->selectedColumns));

            $query = Item::on($dbConn)->select(['items.item_code', 'items.item_name'])
                ->leftJoin('categories', 'items.cat_id', '=', 'categories.id')
                ->leftJoin('families', 'items.family_id', '=', 'families.id')
                ->leftJoin('groups', 'items.group_id', '=', 'groups.id')
                ->addSelect('groups.group_name', 'families.family_name', 'categories.cat_name');

            if (in_array('qty', $finalColumns, true)) {
                $query->addSelect('items.qty');
            }
            if (in_array('cost', $finalColumns, true)) {
                $query->addSelect('items.cost');
            }
            if (in_array('cust_price', $finalColumns, true)) {
                $query->addSelect('items.cust_price');
            }
            if (in_array('term_price', $finalColumns, true)) {
                $query->addSelect('items.term_price');
            }
            if (in_array('cash_price', $finalColumns, true)) {
                $query->addSelect('items.cash_price');
            }

            if ($this->stockFilter === 'gt0') {
                $query->where('items.qty', '>', 0);
            } elseif ($this->stockFilter === 'eq0') {
                $query->where('items.qty', '=', 0);
            }

            if ($this->selectedGroupId) {
                $query->where('items.group_id', $this->selectedGroupId);
            }
            if ($this->selectedFamilyId) {
                $query->where('items.family_id', $this->selectedFamilyId);
            }
            if ($this->selectedCategoryId) {
                $query->where('items.cat_id', $this->selectedCategoryId);
            }

            $query->orderBy('groups.group_name')
                ->orderBy('families.family_name')
                ->orderBy('categories.cat_name')
                ->orderBy('items.item_code');

            $items = $query->get();

            if ($items->isEmpty()) {
                Cache::put($cacheKey, [
                    'status' => 'failed',
                    'message' => 'No items available to generate report.',
                ], now()->addHours(2));
                return;
            }

            Cache::put($cacheKey, [
                'status' => 'processing',
                'message' => 'Rendering PDF...',
                'progress' => 70,
            ], now()->addHours(2));

            // DomPDF can spike memory on large tables. Increase for worker process
            // so the job doesn't crash and get retried until max attempts.
            @ini_set('memory_limit', '-1');

            $availableColumns = [
                'item_code' => 'Stock Code',
                'item_name' => 'Stock Description',
                'qty' => 'Quantity',
                'cost' => 'Cost Price',
                'cash_price' => 'Cash Price',
                'term_price' => 'Term Price',
                'cust_price' => 'Customer',
            ];
            $columnsForView = array_intersect_key($availableColumns, array_flip($finalColumns));

            $showTotals = $this->shouldShowTotals($this->selectedColumns);
            $grandTotal = 0;
            if ($showTotals) {
                foreach ($items as $item) {
                    $grandTotal += (($item->qty ?? 0) * ($item->cost ?? 0));
                }
            }

            $pdf = PDF::loadView('reports.items', [
                'items' => $items,
                'columns' => $columnsForView,
                'companyProfile' => CompanyProfile::on($dbConn)->first(),
                'useGrouping' => $this->showGrouping,
                'showTotals' => $showTotals,
                'grandTotal' => $grandTotal,
            ])->setPaper('a4', 'portrait')->setOptions([
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => false,
                'isPhpEnabled' => true,
                'defaultFont' => 'Arial',
                'dpi' => 72,
                'isJavascriptEnabled' => false,
                'fontCache' => sys_get_temp_dir(),
                'chroot' => base_path(),
            ]);

            $filename = 'inventory_report_' . now()->format('Y-m-d_His') . '.pdf';
            $path = 'reports/' . $filename;
            Storage::disk('local')->put($path, $pdf->output());

            Cache::put($cacheKey, [
                'status' => 'ready',
                'message' => 'PDF is ready to download.',
                'progress' => 100,
                'path' => $path,
                'filename' => $filename,
            ], now()->addHours(2));
        } catch (\Throwable $e) {
            Log::error('Queued inventory PDF generation failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Cache::put($cacheKey, [
                'status' => 'failed',
                'message' => 'Failed to generate PDF: ' . $e->getMessage(),
                'progress' => 0,
            ], now()->addHours(2));
        } finally {
            if (!empty($originalMemoryLimit) && $originalMemoryLimit !== '-1') {
                @ini_set('memory_limit', $originalMemoryLimit);
            }
        }
    }

    public function failed(\Throwable $e): void
    {
        Cache::put($this->cacheKey($this->token), [
            'status' => 'failed',
            'message' => 'Failed to generate PDF: ' . $e->getMessage(),
            'progress' => 0,
        ], now()->addHours(2));

        Log::error('Inventory PDF job failed callback', [
            'token' => $this->token,
            'message' => $e->getMessage(),
        ]);
    }

    private function shouldShowTotals(array $selectedColumns): bool
    {
        $requiredCols = ['item_code', 'item_name'];
        $allowedCols = ['qty', 'cost'];

        $otherCols = array_values(array_diff($selectedColumns, $requiredCols));
        sort($otherCols);
        sort($allowedCols);

        return count($otherCols) === 2
            && in_array('qty', $otherCols, true)
            && in_array('cost', $otherCols, true)
            && $otherCols === $allowedCols;
    }

    private function cacheKey(string $token): string
    {
        return 'inventory_report_pdf:' . $token;
    }

    /**
     * Queued jobs have no HTTP session; match the browser's company DB (SwitchDatabase / active_db).
     */
    private function resolveDatabaseConnection(): string
    {
        if (
            $this->databaseConnection
            && array_key_exists($this->databaseConnection, config('database.connections'))
        ) {
            return $this->databaseConnection;
        }

        return (string) config('database.default');
    }
}
