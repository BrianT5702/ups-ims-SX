<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensure both qty (decimal) and custom_um changes are applied to delivery_orders_items
     * on ALL company connections. The original migrations only ran on the default
     * connection before being updated for multi-connection support.
     */
    public function up(): void
    {
        $connections = ['mysql', 'ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];

        foreach ($connections as $connection) {
            if (!config("database.connections.{$connection}") || !Schema::connection($connection)->hasTable('delivery_orders_items')) {
                continue;
            }

            $schema = Schema::connection($connection);

            // 1. Add custom_um if missing
            if (!$schema->hasColumn('delivery_orders_items', 'custom_um')) {
                $schema->table('delivery_orders_items', function (Blueprint $table) {
                    $table->string('custom_um', 50)->nullable()->after('custom_item_name');
                });
            }

            // 2. Change qty to decimal if still integer
            try {
                $columnType = $schema->getColumnType('delivery_orders_items', 'qty');
                if (in_array($columnType, ['integer', 'int'], true)) {
                    $schema->table('delivery_orders_items', function (Blueprint $table) {
                        $table->decimal('qty', 10, 2)->default(1)->change();
                    });
                }
            } catch (\Throwable $e) {
                // If getColumnType fails or change fails, skip (column may already be decimal)
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't reverse - that would break other connections. Rollback only if needed.
        $connections = ['mysql', 'ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];

        foreach ($connections as $connection) {
            if (!config("database.connections.{$connection}") || !Schema::connection($connection)->hasTable('delivery_orders_items')) {
                continue;
            }

            $schema = Schema::connection($connection);

            if ($schema->hasColumn('delivery_orders_items', 'custom_um')) {
                $schema->table('delivery_orders_items', function (Blueprint $table) {
                    $table->dropColumn('custom_um');
                });
            }

            try {
                $columnType = $schema->getColumnType('delivery_orders_items', 'qty');
                if (in_array($columnType, ['decimal', 'float'], true)) {
                    $schema->table('delivery_orders_items', function (Blueprint $table) {
                        $table->integer('qty')->default(1)->change();
                    });
                }
            } catch (\Throwable $e) {
                // Skip if we can't determine or change column type
            }
        }
    }
};
