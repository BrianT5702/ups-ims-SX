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
        if (Schema::hasTable('quotation_items')) {
            return; // Table already exists
        }
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations');
            $table->foreignId('item_id')->constrained('items');
            $table->string('custom_item_name')->nullable();
            $table->integer('qty')->default(1);
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->string('pricing_tier')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->text('more_description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
