<?php

namespace App\Jobs;

use App\Models\CompanyProfile;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateTransactionPdfReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 1200;

    public function __construct(
        public string $token,
        public array $stockBalances,
        public array $context
    ) {
    }

    public function handle(): void
    {
        $cacheKey = $this->cacheKey($this->token);

        try {
            Cache::put($cacheKey, array_merge(Cache::get($cacheKey, []), [
                'status' => 'processing',
                'message' => 'Preparing PDF data...',
                'progress' => 30,
            ]), now()->addDays(7));

            Cache::put($cacheKey, array_merge(Cache::get($cacheKey, []), [
                'status' => 'processing',
                'message' => 'Rendering PDF...',
                'progress' => 70,
            ]), now()->addDays(7));

            $dbConn = $this->resolveDatabaseConnection();

            $pdf = Pdf::loadView('reports.transactions', [
                'stockBalances' => collect($this->stockBalances),
                'startDate' => $this->context['startDate'] ?? null,
                'endDate' => $this->context['endDate'] ?? null,
                'companyProfile' => CompanyProfile::on($dbConn)->first(),
                'groupName' => $this->context['groupName'] ?? 'ALL',
                'familyName' => $this->context['familyName'] ?? 'ALL',
                'categoryName' => $this->context['categoryName'] ?? 'ALL',
                'companyName' => $this->context['companyName'] ?? 'ALL',
                'stockFilter' => $this->context['stockFilter'] ?? 'ALL',
            ])->setPaper('a4', 'portrait');

            $filename = 'transaction_report_' . now()->format('Y-m-d_His') . '.pdf';
            $path = 'reports/' . $filename;
            Storage::disk('local')->put($path, $pdf->output());

            Cache::put($cacheKey, array_merge(Cache::get($cacheKey, []), [
                'status' => 'ready',
                'message' => 'PDF is ready to download.',
                'progress' => 100,
                'path' => $path,
                'filename' => $filename,
            ]), now()->addDays(7));
        } catch (\Throwable $e) {
            Log::error('Queued transaction PDF generation failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Cache::put($cacheKey, array_merge(Cache::get($cacheKey, []), [
                'status' => 'failed',
                'message' => 'Failed to generate PDF: ' . $e->getMessage(),
                'progress' => 0,
            ]), now()->addDays(7));
        }
    }

    public function failed(\Throwable $e): void
    {
        Cache::put($this->cacheKey($this->token), array_merge(Cache::get($this->cacheKey($this->token), []), [
            'status' => 'failed',
            'message' => 'Failed to generate PDF: ' . $e->getMessage(),
            'progress' => 0,
        ]), now()->addDays(7));
    }

    private function cacheKey(string $token): string
    {
        return 'transaction_report_pdf:' . $token;
    }

    private function resolveDatabaseConnection(): string
    {
        $name = $this->context['databaseConnection'] ?? null;
        if ($name && array_key_exists($name, config('database.connections'))) {
            return $name;
        }

        return (string) config('database.default');
    }
}
