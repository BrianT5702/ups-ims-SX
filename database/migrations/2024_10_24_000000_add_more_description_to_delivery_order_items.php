<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreDescriptionToDeliveryOrderItems extends Migration
{
    public function up()
    {
        Schema::table('delivery_orders_items', function (Blueprint $table) {
            $table->text('more_description')->nullable()->after('remark');
        });
    }

    public function down()
    {
        Schema::table('delivery_orders_items', function (Blueprint $table) {
            $table->dropColumn('more_description');
        });
    }
}
