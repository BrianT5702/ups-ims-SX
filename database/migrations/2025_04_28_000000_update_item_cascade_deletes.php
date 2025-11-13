<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // batch_trackings
        Schema::table('batch_trackings', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
        // purchase_order_items
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
        // delivery_orders_items
        Schema::table('delivery_orders_items', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
        // transactions
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // batch_trackings
        Schema::table('batch_trackings', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->foreign('item_id')->references('id')->on('items');
        });
        // purchase_order_items
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->foreign('item_id')->references('id')->on('items');
        });
        // delivery_orders_items
        Schema::table('delivery_orders_items', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->foreign('item_id')->references('id')->on('items');
        });
        // transactions
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->foreign('item_id')->references('id')->on('items');
        });
    }
}; 