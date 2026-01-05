<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Apply to all database connections (ups, urs, ucs)
        $connections = ['ups', 'urs', 'ucs'];
        
        foreach ($connections as $connection) {
            try {
                if (!Schema::connection($connection)->hasColumn('delivery_orders_items', 'row_index')) {
                    Schema::connection($connection)->table('delivery_orders_items', function (Blueprint $table) {
                        $table->integer('row_index')->nullable()->after('item_id');
                    });
                }
            } catch (\Exception $e) {
                // If connection doesn't exist or table doesn't exist, skip it
                continue;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert on all database connections
        $connections = ['ups', 'urs', 'ucs'];
        
        foreach ($connections as $connection) {
            try {
                if (Schema::connection($connection)->hasColumn('delivery_orders_items', 'row_index')) {
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
