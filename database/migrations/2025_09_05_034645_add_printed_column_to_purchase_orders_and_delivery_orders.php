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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->char('printed', 1)->default('N')->after('status');
        });

        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->char('printed', 1)->default('N')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('printed');
        });

        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropColumn('printed');
        });
    }
};
