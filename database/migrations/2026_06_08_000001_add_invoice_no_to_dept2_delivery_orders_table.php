<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add nullable invoice_no for Department 2 DOs (UPS2, URS2, UCS2).
     */
    public function up(): void
    {
        $connections = ['ups2', 'urs2', 'ucs2'];

        foreach ($connections as $connection) {
            if (!config("database.connections.{$connection}")) {
                continue;
            }

            if (!Schema::connection($connection)->hasTable('delivery_orders')) {
                continue;
            }

            if (Schema::connection($connection)->hasColumn('delivery_orders', 'invoice_no')) {
                continue;
            }

            Schema::connection($connection)->table('delivery_orders', function (Blueprint $table) {
                $table->string('invoice_no')->nullable()->after('cust_po');
            });
        }
    }

    public function down(): void
    {
        $connections = ['ups2', 'urs2', 'ucs2'];

        foreach ($connections as $connection) {
            if (!config("database.connections.{$connection}")) {
                continue;
            }

            if (!Schema::connection($connection)->hasTable('delivery_orders')) {
                continue;
            }

            if (!Schema::connection($connection)->hasColumn('delivery_orders', 'invoice_no')) {
                continue;
            }

            Schema::connection($connection)->table('delivery_orders', function (Blueprint $table) {
                $table->dropColumn('invoice_no');
            });
        }
    }
};
