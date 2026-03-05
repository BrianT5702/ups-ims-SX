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
                Schema::connection($connection)->table('delivery_orders_items', function (Blueprint $table) {
                    $table->decimal('qty', 10, 2)->default(1)->change();
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
            if (config("database.connections.{$connection}") && Schema::connection($connection)->hasTable('delivery_orders_items')) {
                Schema::connection($connection)->table('delivery_orders_items', function (Blueprint $table) {
                    $table->integer('qty')->default(1)->change();
                });
            }
        }
    }
};
