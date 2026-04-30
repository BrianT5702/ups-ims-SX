<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ReportDownloadController extends Controller
{
    public function __invoke(string $token)
    {
        $status = Cache::get($this->inventoryCacheKey($token));

        if (!$status || ($status['status'] ?? null) !== 'ready') {
            abort(404, 'Report is not ready.');
        }

        $path = $status['path'] ?? null;
        $filename = $status['filename'] ?? 'inventory_report.pdf';

        if (!$path || !Storage::disk('local')->exists($path)) {
            abort(404, 'Generated report file not found.');
        }

        return Storage::disk('local')->download($path, $filename);
    }

    public function downloadTransaction(string $token)
    {
        $status = Cache::get($this->transactionCacheKey($token));

        if (!$status || ($status['status'] ?? null) !== 'ready') {
            abort(404, 'Report is not ready.');
        }

        $path = $status['path'] ?? null;
        $filename = $status['filename'] ?? 'transaction_report.pdf';

        if (!$path || !Storage::disk('local')->exists($path)) {
            abort(404, 'Generated report file not found.');
        }

        return Storage::disk('local')->download($path, $filename);
    }

    private function inventoryCacheKey(string $token): string
    {
        return 'inventory_report_pdf:' . $token;
    }

    private function transactionCacheKey(string $token): string
    {
        return 'transaction_report_pdf:' . $token;
    }

    public function downloadFile(string $filename)
    {
        $safeFilename = basename($filename);
        if ($safeFilename !== $filename || !str_ends_with(strtolower($safeFilename), '.pdf')) {
            abort(404, 'Invalid report file.');
        }

        $path = 'reports/' . $safeFilename;
        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'Generated report file not found.');
        }

        return Storage::disk('local')->download($path, $safeFilename);
    }
}
