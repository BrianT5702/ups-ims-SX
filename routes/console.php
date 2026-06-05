<?php

use App\Services\DoNumberService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('do:set-next-number {connection} {nextNumber}', function (string $connection, string $nextNumber) {
    $connection = strtolower($connection);
    $allowed = ['ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];

    if (!in_array($connection, $allowed, true)) {
        $this->error('Connection must be one of: ' . implode(', ', $allowed));

        return 1;
    }

    if (!config("database.connections.{$connection}")) {
        $this->error("Database connection [{$connection}] is not configured.");

        return 1;
    }

    $next = (int) $nextNumber;
    if ($next < 1) {
        $this->error('Next number must be at least 1.');

        return 1;
    }

    DoNumberService::setNextNumber($connection, $next);
    $preview = DoNumberService::getNextDoNumberPreview($connection);
    $this->info("Next DO for [{$connection}] will be: {$preview} (then +1 for each new DO).");

    return 0;
})->purpose('Set the next delivery order number for a company database');
