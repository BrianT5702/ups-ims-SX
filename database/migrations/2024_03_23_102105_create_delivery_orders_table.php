<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->string('ref_num')->unique()->nullable();
            $table->foreignId('cust_id')->constrained('customers');
            $table->foreignId('salesman_id')->constrained('users');
            $table->foreignId('user_id')->constrained('users');
            $table->date('date');
            $table->date('delivery_date')->nullable();
            $table->string('cust_po')->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('do_num')->unique();
            $table->text('remark')->nullable();
            // $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_orders');
    }
}
