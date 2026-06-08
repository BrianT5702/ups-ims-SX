<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dept 2 databases were created after the original nullable migration and may
     * still require item_id NOT NULL. Text-only DO lines need a null item_id.
     */
    public function up(): void
    {
        $connections = ['ups', 'urs', 'ucs', 'ups2', 'urs2', 'ucs2'];

        foreach ($connections as $connection) {
            $this->ensureItemIdNullable($connection);
        }
    }

    public function down(): void
    {
        // No down: making item_id nullable is required for text-only DO lines.
    }

    private function ensureItemIdNullable(string $connection): void
    {
        if (!config("database.connections.{$connection}")) {
            return;
        }

        if (!Schema::connection($connection)->hasTable('delivery_orders_items')) {
            return;
        }

        $database = DB::connection($connection)->getDatabaseName();
        $column = DB::connection($connection)->selectOne(
            'SELECT IS_NULLABLE AS nullable
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$database, 'delivery_orders_items', 'item_id']
        );

        if (!$column || ($column->nullable ?? 'NO') === 'YES') {
            return;
        }

        $foreignKeys = DB::connection($connection)->select(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$database, 'delivery_orders_items', 'item_id']
        );

        foreach ($foreignKeys as $foreignKey) {
            $name = $foreignKey->CONSTRAINT_NAME;
            DB::connection($connection)->statement(
                "ALTER TABLE `delivery_orders_items` DROP FOREIGN KEY `{$name}`"
            );
        }

        DB::connection($connection)->statement(
            'ALTER TABLE `delivery_orders_items` MODIFY `item_id` BIGINT UNSIGNED NULL'
        );

        $remainingFk = DB::connection($connection)->selectOne(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL
             LIMIT 1',
            [$database, 'delivery_orders_items', 'item_id']
        );

        if (!$remainingFk && Schema::connection($connection)->hasTable('items')) {
            DB::connection($connection)->statement(
                'ALTER TABLE `delivery_orders_items`
                 ADD CONSTRAINT `delivery_orders_items_item_id_foreign`
                 FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE'
            );
        }
    }
};
