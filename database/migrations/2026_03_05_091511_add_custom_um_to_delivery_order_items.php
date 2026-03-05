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
        Schema::table('delivery_orders_items', function (Blueprint $table) {
            $table->string('custom_um', 50)->nullable()->after('custom_item_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_orders_items', function (Blueprint $table) {
            $table->dropColumn('custom_um');
        });
    }
};
