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
            if (config("database.connections.{$connection}")) {
                foreach (['delivery_orders', 'quotations', 'purchase_orders'] as $tableName) {
                    if (!Schema::connection($connection)->hasTable($tableName)) {
                        continue;
                    }

                    Schema::connection($connection)->table($tableName, function (Blueprint $table) use ($connection, $tableName) {
                        if (!Schema::connection($connection)->hasColumn($tableName, 'updated_by')) {
                            $table->foreignId('updated_by')->nullable()->after('user_id')->constrained('users');
                        }
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
            if (config("database.connections.{$connection}")) {
                foreach (['delivery_orders', 'quotations', 'purchase_orders'] as $tableName) {
                    if (!Schema::connection($connection)->hasTable($tableName)) {
                        continue;
                    }

                    Schema::connection($connection)->table($tableName, function (Blueprint $table) use ($connection, $tableName) {
                        if (Schema::connection($connection)->hasColumn($tableName, 'updated_by')) {
                            $table->dropForeign(['updated_by']);
                            $table->dropColumn('updated_by');
                        }
                    });
                }
            }
        }
    }
};


