<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connections = ['mysql', 'ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];

        foreach ($connections as $connection) {
            if (config("database.connections.{$connection}") && Schema::connection($connection)->hasTable('delivery_orders_items')) {
                if (!Schema::connection($connection)->hasColumn('delivery_orders_items', 'custom_um')) {
                    Schema::connection($connection)->table('delivery_orders_items', function (Blueprint $table) {
                        $table->string('custom_um', 50)->nullable()->after('custom_item_name');
                    });
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connections = ['mysql', 'ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];

        foreach ($connections as $connection) {
            if (config("database.connections.{$connection}") && Schema::connection($connection)->hasTable('delivery_orders_items')) {
                if (Schema::connection($connection)->hasColumn('delivery_orders_items', 'custom_um')) {
                    Schema::connection($connection)->table('delivery_orders_items', function (Blueprint $table) {
                        $table->dropColumn('custom_um');
                    });
                }
            }
        }
    }
};
