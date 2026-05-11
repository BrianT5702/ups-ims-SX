<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Speed up customer name ordering / filtering in DO (and similar) search.
     */
    public function up(): void
    {
        $connections = ['mysql', 'ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];

        foreach ($connections as $connection) {
            if (!config("database.connections.{$connection}") || !Schema::connection($connection)->hasTable('customers')) {
                continue;
            }

            $schema = Schema::connection($connection);
            if (!$schema->hasColumn('customers', 'cust_name')) {
                continue;
            }

            $schema->table('customers', function (Blueprint $table) {
                $table->index('cust_name', 'customers_cust_name_index');
            });
        }
    }

    public function down(): void
    {
        $connections = ['mysql', 'ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];

        foreach ($connections as $connection) {
            if (!config("database.connections.{$connection}") || !Schema::connection($connection)->hasTable('customers')) {
                continue;
            }

            Schema::connection($connection)->table('customers', function (Blueprint $table) {
                $table->dropIndex('customers_cust_name_index');
            });
        }
    }
};
