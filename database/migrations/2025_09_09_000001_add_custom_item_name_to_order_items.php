<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_order_items', 'custom_item_name')) {
                $table->string('custom_item_name')->nullable()->after('item_id');
            }
        });

        Schema::table('delivery_orders_items', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_orders_items', 'custom_item_name')) {
                $table->string('custom_item_name')->nullable()->after('item_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_order_items', 'custom_item_name')) {
                $table->dropColumn('custom_item_name');
            }
        });

        Schema::table('delivery_orders_items', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_orders_items', 'custom_item_name')) {
                $table->dropColumn('custom_item_name');
            }
        });
    }
};


