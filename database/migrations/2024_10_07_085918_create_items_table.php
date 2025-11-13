<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('item_name');
            $table->string('um');
            $table->integer('qty');
            $table->decimal('cost', 10, 2);
            $table->decimal('cust_price', 10, 2); 
            $table->decimal('term_price', 10, 2);
            $table->decimal('cash_price', 10, 2);
            $table->integer('stock_alert_level')->nullable();
            $table->foreignId('sup_id')->nullable()->constrained('suppliers');
            $table->foreignId('cat_id')->nullable()->constrained('categories');
            $table->foreignId('brand_id')->nullable()->constrained('brands');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses');
            $table->foreignId('location_id')->nullable()->constrained('locations');
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['sup_id']);
            $table->dropForeign(['cat_id']);
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['warehouse_id']);
            $table->dropForeign(['location_id']);
        });
    
        Schema::dropIfExists('items');
    }
}
