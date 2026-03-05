<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ensure DO sequence start values match config (only if still at default 1).
     */
    public function up(): void
    {
        $connections = ['mysql', 'ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];

        foreach ($connections as $connection) {
            if (!config("database.connections.{$connection}")) {
                continue;
            }

            if (!\Schema::connection($connection)->hasTable('do_number_sequences')) {
                continue;
            }

            $row = DB::connection($connection)->table('do_number_sequences')->first();
            $start = config("do.start.{$connection}", 1);

            if ($row && (int) $row->next_number === 1 && $start !== 1) {
                DB::connection($connection)->table('do_number_sequences')
                    ->where('id', $row->id)
                    ->update(['next_number' => $start, 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        // No revert needed
    }
};
