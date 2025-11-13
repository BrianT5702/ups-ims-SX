<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryOrderItemsTable extends Migration
{
    public function up()
    {
        Schema::create('delivery_orders_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('do_id')->constrained('delivery_orders');
            $table->foreignId('item_id')->constrained('items');
            $table->integer('qty')->default(1);
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_orders_items');
    }
}
