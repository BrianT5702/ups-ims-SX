<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Earlier migration (2026_01_05) only added row_index on ups/urs/ucs.
     * Department 2 databases (ups2, urs2, ucs2) need the same column for the DO form.
     */
    public function up(): void
    {
        $connections = ['ups2', 'urs2', 'ucs2'];

        foreach ($connections as $connection) {
            try {
                if (!Schema::connection($connection)->hasTable('delivery_orders_items')) {
                    continue;
                }

                if (!Schema::connection($connection)->hasColumn('delivery_orders_items', 'row_index')) {
                    Schema::connection($connection)->table('delivery_orders_items', function (Blueprint $table) {
                        $table->integer('row_index')->nullable()->after('item_id');
                    });
                }
            } catch (\Exception $e) {
                continue;
            }
        }
    }

    public function down(): void
    {
        $connections = ['ups2', 'urs2', 'ucs2'];

        foreach ($connections as $connection) {
            try {
                if (
                    Schema::connection($connection)->hasTable('delivery_orders_items')
                    && Schema::connection($connection)->hasColumn('delivery_orders_items', 'row_index')
                ) {
                    Schema::connection($connection)->table('delivery_orders_items', function (Blueprint $table) {
                        $table->dropColumn('row_index');
                    });
                }
            } catch (\Exception $e) {
                continue;
            }
        }
    }
};
