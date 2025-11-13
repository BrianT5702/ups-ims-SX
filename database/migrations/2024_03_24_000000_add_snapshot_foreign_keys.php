<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->foreignId('customer_snapshot_id')->nullable()->constrained('customer_snapshots');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('supplier_snapshot_id')->nullable()->constrained('supplier_snapshots');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropForeign(['customer_snapshot_id']);
            $table->dropColumn('customer_snapshot_id');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['supplier_snapshot_id']);
            $table->dropColumn('supplier_snapshot_id');
        });
    }
}; 