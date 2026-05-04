<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * purchase_orders.is_updated was first added only on the default connection.
     * Tenant DBs (ups, urs, ucs, …) need the same column.
     */
    public function up(): void
    {
        $connections = ['mysql', 'ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];

        foreach ($connections as $connection) {
            if (! config("database.connections.{$connection}")) {
                continue;
            }
            if (! Schema::connection($connection)->hasTable('purchase_orders')) {
                continue;
            }
            if (Schema::connection($connection)->hasColumn('purchase_orders', 'is_updated')) {
                continue;
            }

            Schema::connection($connection)->table('purchase_orders', function (Blueprint $table) {
                $table->char('is_updated', 1)->default('N')->after('printed');
            });
        }
    }

    public function down(): void
    {
        $connections = ['mysql', 'ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];

        foreach ($connections as $connection) {
            if (! config("database.connections.{$connection}")) {
                continue;
            }
            if (! Schema::connection($connection)->hasTable('purchase_orders')) {
                continue;
            }
            if (! Schema::connection($connection)->hasColumn('purchase_orders', 'is_updated')) {
                continue;
            }

            Schema::connection($connection)->table('purchase_orders', function (Blueprint $table) {
                $table->dropColumn('is_updated');
            });
        }
    }
};
