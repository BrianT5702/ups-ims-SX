<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ReportDownloadController extends Controller
{
    public function __invoke(string $token)
    {
        $status = Cache::get($this->cacheKey($token));

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

    private function cacheKey(string $token): string
    {
        return 'inventory_report_pdf:' . $token;
    }
}
