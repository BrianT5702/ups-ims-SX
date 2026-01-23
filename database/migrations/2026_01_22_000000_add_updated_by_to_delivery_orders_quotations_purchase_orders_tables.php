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
                Schema::connection($connection)->table('delivery_orders', function (Blueprint $table) use ($connection) {
                    if (!Schema::connection($connection)->hasColumn('delivery_orders', 'updated_by')) {
                        $table->foreignId('updated_by')->nullable()->after('user_id')->constrained('users');
                    }
                });

                Schema::connection($connection)->table('quotations', function (Blueprint $table) use ($connection) {
                    if (!Schema::connection($connection)->hasColumn('quotations', 'updated_by')) {
                        $table->foreignId('updated_by')->nullable()->after('user_id')->constrained('users');
                    }
                });

                Schema::connection($connection)->table('purchase_orders', function (Blueprint $table) use ($connection) {
                    if (!Schema::connection($connection)->hasColumn('purchase_orders', 'updated_by')) {
                        $table->foreignId('updated_by')->nullable()->after('user_id')->constrained('users');
                    }
                });
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
                Schema::connection($connection)->table('delivery_orders', function (Blueprint $table) use ($connection) {
                    if (Schema::connection($connection)->hasColumn('delivery_orders', 'updated_by')) {
                        $table->dropForeign(['updated_by']);
                        $table->dropColumn('updated_by');
                    }
                });

                Schema::connection($connection)->table('quotations', function (Blueprint $table) use ($connection) {
                    if (Schema::connection($connection)->hasColumn('quotations', 'updated_by')) {
                        $table->dropForeign(['updated_by']);
                        $table->dropColumn('updated_by');
                    }
                });

                Schema::connection($connection)->table('purchase_orders', function (Blueprint $table) use ($connection) {
                    if (Schema::connection($connection)->hasColumn('purchase_orders', 'updated_by')) {
                        $table->dropForeign(['updated_by']);
                        $table->dropColumn('updated_by');
                    }
                });
            }
        }
    }
};


