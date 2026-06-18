<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The prior migration may have run only on the default connection.
     * Apply quotation_items DO-style columns on every company database.
     */
    public function up(): void
    {
        $connections = ['mysql', 'ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];

        foreach ($connections as $connection) {
            try {
                if (! Schema::connection($connection)->hasTable('quotation_items')) {
                    continue;
                }

                if (! Schema::connection($connection)->hasColumn('quotation_items', 'custom_um')) {
                    Schema::connection($connection)->table('quotation_items', function (Blueprint $table) {
                        $table->string('custom_um')->nullable()->after('custom_item_name');
                    });
                }

                if (! Schema::connection($connection)->hasColumn('quotation_items', 'row_index')) {
                    Schema::connection($connection)->table('quotation_items', function (Blueprint $table) {
                        $table->integer('row_index')->nullable()->after('item_id');
                    });
                }

                if (Schema::connection($connection)->hasColumn('quotation_items', 'item_id')) {
                    Schema::connection($connection)->table('quotation_items', function (Blueprint $table) {
                        $table->unsignedBigInteger('item_id')->nullable()->change();
                    });
                }
            } catch (\Exception $e) {
                continue;
            }
        }
    }

    public function down(): void
    {
        // No down — columns are shared with the primary migration intent.
    }
};
