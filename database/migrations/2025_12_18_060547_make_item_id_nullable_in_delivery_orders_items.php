<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Apply to all database connections (ups, urs, ucs)
        $connections = ['ups', 'urs', 'ucs'];
        
        foreach ($connections as $connection) {
            try {
                // Get the actual foreign key constraint name
                $constraintName = DB::connection($connection)->selectOne("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'delivery_orders_items' 
                    AND COLUMN_NAME = 'item_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                if ($constraintName) {
                    $constraintName = $constraintName->CONSTRAINT_NAME;
                    // Drop the foreign key constraint
                    DB::connection($connection)->statement("ALTER TABLE `delivery_orders_items` DROP FOREIGN KEY `{$constraintName}`");
                }
                
                // Make item_id nullable
                DB::connection($connection)->statement('ALTER TABLE `delivery_orders_items` MODIFY `item_id` BIGINT UNSIGNED NULL');
                
                // Re-add the foreign key constraint (allows null values)
                DB::connection($connection)->statement('ALTER TABLE `delivery_orders_items` ADD CONSTRAINT `delivery_orders_items_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE');
            } catch (\Exception $e) {
                // If connection doesn't exist or table doesn't exist, skip it
                continue;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert on all database connections
        $connections = ['ups', 'urs', 'ucs'];
        
        foreach ($connections as $connection) {
            try {
                DB::connection($connection)->statement('ALTER TABLE `delivery_orders_items` DROP FOREIGN KEY `delivery_orders_items_item_id_foreign`');
                DB::connection($connection)->statement('ALTER TABLE `delivery_orders_items` MODIFY `item_id` BIGINT UNSIGNED NOT NULL');
                DB::connection($connection)->statement('ALTER TABLE `delivery_orders_items` ADD CONSTRAINT `delivery_orders_items_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE');
            } catch (\Exception $e) {
                continue;
            }
        }
    }
};
