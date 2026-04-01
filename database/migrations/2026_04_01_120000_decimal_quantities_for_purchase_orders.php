<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allow fractional order/receive quantities on PO (aligned with DO / purchasing needs).
     */
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->default(1)->change();
            $table->decimal('total_qty_received', 10, 2)->default(0)->change();
        });

        Schema::table('batch_trackings', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->change();
        });

        Schema::table('items', function (Blueprint $table) {
            $table->decimal('qty', 12, 2)->default(0)->change();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('qty_on_hand', 12, 2)->change();
            $table->decimal('qty_before', 12, 2)->change();
            $table->decimal('qty_after', 12, 2)->change();
            $table->decimal('transaction_qty', 12, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->integer('quantity')->default(1)->change();
            $table->integer('total_qty_received')->default(0)->change();
        });

        Schema::table('batch_trackings', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });

        Schema::table('items', function (Blueprint $table) {
            $table->integer('qty')->default(0)->change();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->integer('qty_on_hand')->change();
            $table->integer('qty_before')->change();
            $table->integer('qty_after')->change();
            $table->integer('transaction_qty')->change();
        });
    }
};
