<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connections = ['mysql', 'ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];
        $importBatch = 'BATCH-00000000-000';

        foreach ($connections as $connection) {
            if (! config("database.connections.{$connection}")
                || ! Schema::connection($connection)->hasTable('batch_trackings')) {
                continue;
            }

            $schema = Schema::connection($connection);

            if (! $schema->hasColumn('batch_trackings', 'original_quantity')) {
                $schema->table('batch_trackings', function (Blueprint $table) {
                    $table->decimal('original_quantity', 12, 2)
                        ->nullable()
                        ->after('quantity');
                });
            }

            // Best-effort backfill: import batches driven negative by oversell → opening 0.
            DB::connection($connection)->table('batch_trackings')
                ->whereNull('original_quantity')
                ->update([
                    'original_quantity' => DB::raw(
                        "CASE WHEN batch_num = '{$importBatch}' AND quantity < 0 THEN 0 ELSE quantity END"
                    ),
                ]);
        }
    }

    public function down(): void
    {
        $connections = ['mysql', 'ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];

        foreach ($connections as $connection) {
            if (! config("database.connections.{$connection}")
                || ! Schema::connection($connection)->hasTable('batch_trackings')) {
                continue;
            }

            if (Schema::connection($connection)->hasColumn('batch_trackings', 'original_quantity')) {
                Schema::connection($connection)->table('batch_trackings', function (Blueprint $table) {
                    $table->dropColumn('original_quantity');
                });
            }
        }
    }
};
