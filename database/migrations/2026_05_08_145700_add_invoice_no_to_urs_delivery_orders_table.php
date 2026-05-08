<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing invoice_no column for URS connection.
     */
    public function up(): void
    {
        $connection = 'urs';

        if (!config("database.connections.{$connection}")) {
            return;
        }

        if (!Schema::connection($connection)->hasTable('delivery_orders')) {
            return;
        }

        if (Schema::connection($connection)->hasColumn('delivery_orders', 'invoice_no')) {
            return;
        }

        Schema::connection($connection)->table('delivery_orders', function (Blueprint $table) {
            $table->string('invoice_no')->nullable()->after('cust_po');
        });
    }

    public function down(): void
    {
        $connection = 'urs';

        if (!config("database.connections.{$connection}")) {
            return;
        }

        if (!Schema::connection($connection)->hasTable('delivery_orders')) {
            return;
        }

        if (!Schema::connection($connection)->hasColumn('delivery_orders', 'invoice_no')) {
            return;
        }

        Schema::connection($connection)->table('delivery_orders', function (Blueprint $table) {
            $table->dropColumn('invoice_no');
        });
    }
};
