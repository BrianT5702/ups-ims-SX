<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeRefNumNotUniqueInDeliveryOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * This will drop the unique constraint on ref_num so that
     * multiple delivery orders can share the same reference number.
     */
    public function up()
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            // The index name comes from the original migration:
            // $table->string('ref_num')->unique()->nullable();
            // which creates 'delivery_orders_ref_num_unique'
            $table->dropUnique('delivery_orders_ref_num_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * This will restore the unique constraint on ref_num.
     */
    public function down()
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->unique('ref_num');
        });
    }
}

