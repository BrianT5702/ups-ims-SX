<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveRemarkColumnsFromOrderItemsTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('delivery_orders_items', function (Blueprint $table) {
            $table->dropColumn('remark');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn('remark');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_orders_items', function (Blueprint $table) {
            $table->text('remark')->nullable()->after('amount');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->text('remark')->nullable()->after('total_price_line_item');
        });
    }
}
